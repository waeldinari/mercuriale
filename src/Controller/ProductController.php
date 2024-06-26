<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Entity\Prices;
use App\Form\MercurialeImportType;
use App\Form\ProductType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="product_index")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->getDoctrine()->getRepository(Product::class)->findAll();

        $products = $paginator->paginate(
            $query, // Requête à paginer
            $request->query->getInt('page', 1), // Numéro de page
            10 // Nombre d'éléments par page
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/product/add", name="product_add", methods={"GET", "POST"})
     */
    public function add(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product added successfully.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product/edit/{id}", name="product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Product updated successfully.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/products/search", name="product_search")
     */
    public function search(Request $request, PaginatorInterface $paginator): Response
    {
        // Ici on récupère le terme de recherche depuis la requête HTTP
        $searchTerm = $request->query->get('q', '');
        // Ici on récupère le type de recherche (description ou code produit)
        $searchType = $request->query->get('search_type', 'name');

        $query = $this->getDoctrine()->getRepository(Product::class)->createQueryBuilder('p');

        if ($searchTerm) {
            if ($searchType === 'name') {
                $query->andWhere('p.name LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            } elseif ($searchType === 'product_code') {
                $query->andWhere('p.code LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            }
        }

        // Utilisation de KnpPaginator pour paginer les résultats
        $pagination = $paginator->paginate(
            $query->getQuery(), // Requête à paginer
            $request->query->getInt('page', 1), // Numéro de page par défaut
            10 // Limite par page
        );

        return $this->render('product/search.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'searchType' => $searchType,
        ]);
    }

    /**
     * @Route("/products/import", name="product_import", methods={"GET", "POST"})
     */
    public function import(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(MercurialeImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $supplierId = $form->get('supplier')->getData(); // Récupérer l'ID du fournisseur

            $entityManager = $this->getDoctrine()->getManager();

            // On récupère l'entité Supplier correspondant à l'ID
            $supplier = $entityManager->getRepository(Supplier::class)->find($supplierId);

            // Ici je vérifie si le fournisseur existe
            if (!$supplier) {
                throw $this->createNotFoundException('Supplier not found');
            }

            // Traitement de l'importation du fichier CSV
            $this->importMercuriale($file, $supplier);

            // Envoi de l'e-mail de confirmation
            $this->sendConfirmationEmail($supplier, $mailer);

            $this->addFlash('success', 'Mercuriale importée avec succès.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// Fonction pour envoyer l'e-mail de confirmation
    private function sendConfirmationEmail($supplier, MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('wael.dinari5@gmail.com')
            ->to('wael.dinari5@gmail.com')
            ->subject('Confirmation d\'importation de la mercuriale')
            ->html('<p>Votre importation de la mercuriale a été effectuée avec succès.</p>');

        $mailer->send($email);
    }



    private function importMercuriale($file, $supplier)
    {
        $csvData = file_get_contents($file->getPathname());
        $rows = array_map('str_getcsv', explode("\n", $csvData));

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($rows as $row) {
            // Check si la ligne n'est pas vide
            if (!empty($row)) {
                // Vérification si tous les indices nécessaires existent dans la ligne
                if (isset($row[0]) && isset($row[1]) && isset($row[2])) {
                    $productCode = $row[0];
                    $productName = $row[1];
                    $price = (float) $row[2]; // Ceci est pour convertir la valeur de la chaîne en float

                    // Rechercher le produit dans la base de données
                    $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => $productCode]);

                    // Si le produit n'existe pas, donc je dois le créer
                    if (!$product) {
                        $product = new Product();
                        $product->setCode($productCode);
                    }

                    //Ici la mise à jour les informations du produit
                    $product->setName($productName);
                    $product->setPrice($price);
                    $product->setSupplier($supplier);
                    $entityManager->persist($product);

                    // Ici j'ai créer une nouvelle instance de Prices
                    $priceEntity = new Prices();
                    $priceEntity->setProduct($product);
                    $priceEntity->setPrice($price);
                    $priceEntity->setTimestamp(new \DateTime()); // Save date timestamp

                    // Persistez l'instance de Prices
                    $entityManager->persist($priceEntity);
                } else {
                    continue;
                }
            }
        }

        $entityManager->flush();
    }


}
