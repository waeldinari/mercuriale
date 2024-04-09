<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 */

namespace App\Form;

use App\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;

class MercurialeImportType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();

        $supplierChoices = [];
        foreach ($suppliers as $supplier) {
            $supplierChoices[$supplier->getName()] = $supplier;
        }

        $builder
            ->add('file', FileType::class, [
                'label' => 'Select CSV file',
            ])
            ->add('supplier', ChoiceType::class, [
                'choices' => $supplierChoices,
                'choice_label' => function ($supplier, $key, $value) {
                    return $supplier->getName(); // Ceci est utilisé pour afficher le nom du fournisseur dans le champ
                },
                'label' => 'Select Supplier',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
