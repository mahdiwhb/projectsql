<?php
// Inclusion de Faker
require_once 'vendor/autoload.php';
$faker = Faker\Factory::create();

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=ecommerce_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Connexion réussie à la base de données.</h2>";

    // Barre de recherche
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    echo "
        <form method='GET'>
            <input type='text' name='search' placeholder='Rechercher...' value='" . htmlspecialchars($searchTerm, ENT_QUOTES) . "'>
            <input type='submit' value='Rechercher'>
        </form>
    ";

    // Fonction pour afficher une table
    function displayTable($data, $headers) {
        if (empty($data)) {
            echo "<p>Aucun résultat trouvé.</p>";
            return;
        }
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr>";
        foreach ($headers as $header) {
            echo "<th style='padding: 8px; background-color: #f2f2f2;'>$header</th>";
        }
        echo "</tr>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td style='padding: 8px;'>$cell</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    }

    // Rechercher et afficher les utilisateurs
    $userQuery = "SELECT user_id, first_name, last_name, email, created_at FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute(["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"]);
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Utilisateurs</h3>";
    displayTable($users, ['user_id', 'Prénom', 'Nom', 'Email', 'Créé le']);

    // Rechercher et afficher les produits
    $productQuery = "SELECT product_id, name, description, price, stock_quantity, created_at FROM products WHERE name LIKE ? OR description LIKE ?";
    $productStmt = $pdo->prepare($productQuery);
    $productStmt->execute(["%$searchTerm%", "%$searchTerm%"]);
    $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Produits</h3>";
    displayTable($products, ['product_id', 'Nom', 'Description', 'Prix', 'Quantité', 'Créé le']);

    // Rechercher et afficher les commandes
    $commandQuery = "SELECT command_id, user_id, status, created_at FROM commands WHERE status LIKE ?";
    $commandStmt = $pdo->prepare($commandQuery);
    $commandStmt->execute(["%$searchTerm%"]);
    $commands = $commandStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Commandes</h3>";
    displayTable($commands, ['command_id', 'ID Utilisateur', 'Statut', 'Créé le']);

    // Rechercher et afficher les paniers (nouvelle structure cart_products)
    $cartQuery = "SELECT id, cart_id, product_id, quantity, added_at FROM carts_products WHERE cart_id LIKE ? OR product_id LIKE ?";
    $cartStmt = $pdo->prepare($cartQuery);
    $cartStmt->execute(["%$searchTerm%", "%$searchTerm%"]);
    $carts = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Paniers</h3>";
    if (!empty($carts)) {
        displayTable($carts, ['id', 'ID Panier', 'ID Produit', 'Quantité', 'Ajouté le']);
    } else {
        echo "<p>Aucun panier correspondant trouvé.</p>";
    }

    // Rechercher et afficher les factures
    $invoiceQuery = "SELECT invoice_id, command_id, total, issued_at FROM invoices WHERE total LIKE ?";
    $invoiceStmt = $pdo->prepare($invoiceQuery);
    $invoiceStmt->execute(["%$searchTerm%"]);
    $invoices = $invoiceStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Factures</h3>";
    displayTable($invoices, ['invoice_id', 'ID Commande', 'Total', 'Émis le']);

    // Rechercher et afficher les photos
    $photoQuery = "SELECT p.photo_id, p.product_id, p.user_id, p.url, pt.type_name 
                   FROM photos p 
                   JOIN photo_types pt ON p.type_id = pt.type_id 
                   WHERE p.url LIKE ? OR pt.type_name LIKE ?";
    $photoStmt = $pdo->prepare($photoQuery);
    $photoStmt->execute(["%$searchTerm%", "%$searchTerm%"]);
    $photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Photos</h3>";
    displayTable($photos, ['photo_id', 'ID Produit', 'ID Utilisateur', 'URL', 'Type de Photo']);

} catch (PDOException $e) {
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
}
