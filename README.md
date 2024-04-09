# Projet Mercuriale

Ce projet Symfony est une application Web développée pour foodMarket.

## Requirements
    PHP 7.4.11 (cli)
    Symfony 5.4.38 (env: dev, debug: true)
    XAMPP for Windows 8.2.12
    Yarn 1.22.4
    
## To check the Symfony and PHP versions of our project
    php bin/console --version
    php -v
    
## Installation

Pour installer ce projet, suivez les étapes suivantes :

1. Clonez ce dépôt vers votre machine locale.
2. Installez les dépendances en utilisant Composer :
    ```
    composer install
    ```
## The development steps and setup of our application are as follows:    

1. Pour commencer :
    ```  
    symfony new projet_mercuriale --full
    composer create-project symfony/skeleton projet_mercuriale
    cd projet_mercuriale
    php bin/console make:entity Product
    php bin/console make:controller ProductController
    php bin/console doctrine:database:create (ToDo in next step BDD)
    php bin/console doctrine:schema:update --force
    php bin/console make:entity Supplier
    php bin/console cache:clear
    ```
    
2. Quelques astuces pour résoudre le problème de dépendance si on va choisir la version 5.4 :
   ```
    composer require symfony/maker-bundle --dev
    composer require symfony/orm-pack
    composer require symfony/maker-bundle:^1.42 --with-all-dependencies
    ```

## To define the relationship in the 'Product' entity

1. Mise à jour de l'entité src/Entity/Product.php:

    ```
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;
    php bin/console doctrine:schema:update --force
    ```
    
## Replace DATABASE_URL with your database URL

1. Ouvrez le fichier .env situé à la racine de votre projet Symfony.
    
    ```
    DATABASE_URL="mysql://root:@127.0.0.1:3306/db_mercuriale"
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    ```
    
2. Démarrez le serveur de développement intégré en utilisant la commande Symfony server:start 
    
    ```
    php bin/console server:start
    symfony server:start
    ```
    
## Installing the KnpPaginatorBundle bundle

1. J'ai commencé par l'installation du bundle KnpPaginatorBundle à l'aide de Composer :
 
    ```        
    composer require knplabs/knp-paginator-bundle
    ```

2. Mise à jour dans src/Controller/ProductController.php

    ```
    use Knp\Component\Pager\PaginatorInterface;
    $products = $paginator->paginate(
            $query, // Requête à paginer
            $request->query->getInt('page', 1), // Numéro de page
            10 // Nombre d'éléments par page
        );
    ```
    
## Developing the search by filter functionality using pagination
    
1. Implimentation de la function search()

2. Remarque:

   La variable $searchType est utilisée pour récupérer le type de recherche (description ou code produit)

## Developing the functionality for importing CSV files

1. J'ai utilisé la commande pour créer le formulaire: 
 
    ```
    php bin/console make:form MercurialeImportType
    ```
    
2. Pour récupérer tous les fournisseurs depuis la base de donnée en tant qu'un objet : 

    ```
    ->add('supplier', ChoiceType::class, [
                'choices' => $supplierChoices,
                'choice_label' => function ($supplier, $key, $value) {
                    return $supplier->getName(); // Ici pour afficher le nom du fournisseur dans le champ
                },
                'label' => 'Select Supplier',
            ]);

    ```
3. Implimentation de la function import()
4. Implimentation de la function function importMercuriale()

5. Remarque :

   J'ai ajouté une méthode privée importMercuriale() qui effectue le traitement de l'importation du fichier CSV conformément aux règles fonctionnelles que vous avez fournies
   Une fois que le traitement est terminé, j'ai ajouté un message flash de succès et redirigeons l'utilisateur vers la page d'index des produits.


## Developing the command for importing CSV files

1. Etape n°1, dans notre projet Symfony, j'ai stocké les fichiers CSV dans le répertoire public 

## Creating a command for file importation & using the Symfony Messenger component

  ```
  php bin/console make:command ImportCsvCommand
  ```
## Creating a message for CSV importation

  ```
  src/Message
  ```
    
## Creating a message handler for CSV importation
  
 ```
 src/MessageHandler
 ```
 
## Configuring the Messenger component
  
  ```
  config/packages/messenger.yaml
  ```