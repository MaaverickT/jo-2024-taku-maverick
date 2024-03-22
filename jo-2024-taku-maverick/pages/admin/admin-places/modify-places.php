<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
}

$id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);
// LIGNE A SUPPRIMER APRES AVOIR RESOLU LE PROBLEME


// Vérifiez si l'ID du lieu est un entier valide
if (!$id_lieu && $id_lieu !== 0) {
    $_SESSION['error'] = "ID du lieu invalide.";
    header("Location: manage-places.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_STRING);
    $adresseLieu = filter_input(INPUT_POST, 'adresseLieu', FILTER_SANITIZE_STRING);
    $cpLieu = filter_input(INPUT_POST, 'cpLieu', FILTER_SANITIZE_STRING);
    $villeLieu = filter_input(INPUT_POST, 'villeLieu', FILTER_SANITIZE_STRING);


    // Vérifiez si le nom du lieu est vide
    if (empty($nomLieu) || empty($adresseLieu) || empty($cpLieu) || empty($villeLieu)) {
        $_SESSION['error'] = "L'un des champs ne peut pas être vide.";
        header("Location: add-places.php");
        exit();
    }

    try {
        // Vérifiez si le lieu existe déjà
        $queryCheck = "SELECT id_lieu FROM LIEU WHERE nom_lieu = :nomLieu AND adresse_lieu = :adresseLieu AND cp_lieu = :cpLieu AND ville_lieu = :villeLieu AND id_lieu <> :idLieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le lieu existe déjà.";
            header("Location: modify-places.php?id_lieu=$id_lieu");
            exit();
        }

        // Requête pour mettre à jour le lieu
        $query = "UPDATE LIEU SET nom_lieu = :nomLieu, adresse_lieu = :adresseLieu, cp_lieu = :cpLieu, ville_lieu = :villeLieu WHERE id_lieu = :idLieu";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statement->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
        $statement->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);
        $statement->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
        $statement->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le Lieu a été modifié avec succès.";
            header("Location: manage-places.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de.";
            header("Location: modify-places.php?id_lieu=$id_lieu");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-places.php");
        exit();
    }
}

// Récupérez les informations de l'user pour affichage dans le formulaire
try {
    $queryLieu = "SELECT nom_lieu, adresse_lieu, cp_lieu, ville_lieu FROM LIEU WHERE id_lieu = :idLieu";
    $statementLieu = $connexion->prepare($queryLieu);
    $statementLieu->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
    $statementLieu->execute();

    if ($statementLieu->rowCount() > 0) {
        $lieu = $statementLieu->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Lieu non trouvé.";
        header("Location: manage-places.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-places.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Modifier un Lieu- Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-users.php">Gestion Utilisateur</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="manage-countries.php">Gestion Pays</a></li>
                <li><a href="manage-gender.php">Gestion Genres</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier un lieu</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <!-- FORMULAIRE POUR LA DEMANDE DE MODIFICATION D'UN LIEU -->
        <form action="modify-places.php?id_lieu=<?php echo $id_lieu; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu?')">
            <label for="nomLieu">Nom du lieu :</label>
            <input type="text" name="nomLieu" id="nomLieu" value="<?php echo htmlspecialchars($lieu['nom_lieu']); ?>"
                required>

            <label for="adresseLieu">Adresse de lieu :</label>
            <input type="text" name="adresseLieu" id="adresseLieu"
                value="<?php echo htmlspecialchars($lieu['adresse_lieu']); ?>" required>

            <label for="cpLieu">Code Postal :</label>
            <input type="text" name="cpLieu" id="cpLieu" value="<?php echo htmlspecialchars($lieu['cp_lieu']); ?>"
                required>
            
            <label for="villeLieu">Ville : </label>
            <input type="text" name="villeLieu" id="villeLieu"
                value="<?php echo htmlspecialchars($lieu['ville_lieu']); ?>" required>


            <input type="submit" value="Modifier un lieu">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des Lieux</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>