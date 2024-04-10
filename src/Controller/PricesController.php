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
use App\Entity\Prices;
use Knp\Component\Pager\PaginatorInterface;

class PricesController extends AbstractController
{
    /**
     * @Route("/prices", name="app_prices")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->getDoctrine()->getRepository(Prices::class)->findAll();

        $prices = $paginator->paginate(
            $query, // Requête à paginer
            $request->query->getInt('page', 1), // Numéro de page
            10 // Nombre d'éléments par page
        );

        return $this->render('prices/index.html.twig', [
            'prices' => $prices,
        ]);
    }
}
