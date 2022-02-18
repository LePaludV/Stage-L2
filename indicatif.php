<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet" type="text/css">
    <link href="bootstrap.css" rel="stylesheet"> 
    <title>Indicatif</title>
</head>
<body class="d-flex text-center text-white bg-dark ">
    <main class="center">
        <h1 >Numéro international</h1>
    
    <?php if(empty($paysUser) and empty($_POST['numéro'])): ?>
    <!--Cette première partie est affichée quand l'utilisateur n'a pas rentré de pays et de numéro de téléphone, c'est donc ce qui est affiché par défaut. -->
        <p>Choisissez un pays et entrer le numéro de téléphone que vous souhaitez afin d'obtenir le numéro de téléphone avec l'indicatif du pays sélectionné.</p>
        <form action="/indicatif/indicatif.php" method="POST">
        <div class="list row g-3 align-items-center">
                <div class="col-auto">
                    <label for="pays" class="col-form-label">Veuillez choisir un pays :</label>
                </div>
        
                <div class="col-auto">
                <select name="pays" id="pays" class="form-select" required>
                    <option selected disabled >-----Pays-----</option>';
                    <?php
                        // Je récupère la base de données.
                        try
                        {
                            $bdd = new PDO('mysql:host= .mysql.db;dbname= ;charset=utf8', ' ', ' ');
                        }
                        catch (Exception $e)
                        {
                                die('Erreur : ' . $e->getMessage());
                        }
                        $requete =$bdd->query('SELECT pays from indicatif ');
                        // Je parcours tous les pays de ma table "indicatif" et pour chaque pays qu'il y a j'ajoute une option dans la liste déroulante avec le pays.
                        while($donnée=$requete->fetch()){           
                            
                            echo '<option value="'.$donnée['pays'].'">'.$donnée['pays'].'</option>';
                        }
                    ?>
                </select>
                </div>
            </div>


        
                
            <div class="num row g-3 align-items-center">
                <div class="col-auto">
                    <label for="numéro" class="col-form-label">Numéro de téléphone :</label>
                </div>
                <div class="col-auto">
                    <!-- Création d'un input pour le numéro de téléphone obligatoire avec uniquement des chiffres -->
                    <input class="form-control" type="tel" id="numéro" name="numéro" required pattern="[0-9]{1,}" title="Chiffres uniquement">
                </div>
                <div class="col-auto">
                    <span id="passwordHelpInline" class="form-text">
                    Chiffres uniquement
                    </span>
                </div>
            </div>
                

            
            <div class="button">
                <p><button type="submit"  class="btn btn-secondary " >Envoyer</button></p>
            </div>
                        
        </form>
    <?php else: ?>
        <?php 
        /*Cette 2ème partie est affiché lorsque l'utilisateur appuie sur le boutton submit pour envoyer, le pays et le numéro sont remplie donc on passe dans le else.
        Dans ce else on va récuperer le numéro et le pays que l'utilisateur à rentrer */
            
            $numUser=htmlspecialchars($_POST['numéro']);
            $paysUser=htmlspecialchars($_POST['pays']);
                try
                {
                     $bdd = new PDO('mysql:host= .mysql.db;dbname= ;charset=utf8', ' ', ' ');
                }
                 catch (Exception $e)
                {
                        die('Erreur : ' . $e->getMessage());
                }
                $indicatif = $bdd->prepare('SELECT indicatif FROM indicatif where pays = ?');
                $indicatif-> execute(array($paysUser));
                //Ici on récupère l'indicatif du pays choisi 
                while ($donnees = $indicatif->fetch()){
                $ind=$donnees['indicatif'];}
                $indicatif->closeCursor();
                
                /*En faisant des recherches sur les numéros indicatifs je me suis rendu compte qu'il y avait des exceptions pour la France et la Belgique.
                Leur numéro national commence par 0... mais le numéro international n'a pas ce 0 au début (France national : 06. XX. XX... international +336XXXX...) 
                Donc dans ce if je regarde si le pays choisi est la France ou la Belgique et s'il commence par 0, si c'est le cas j'enlève le 0 du début.
                */
                if((($paysUser=='France métropolitaine') or ($paysUser=='Belgique') ) and (substr($numUser,0,1)==0) ){
                    $numFinal=substr($numUser,1,);
                    $numUser-=1;

                }
                else{$numFinal=$numUser;}
                echo '<p> Voici le numéro de téléphone avec l\'indicatif du pays choisi :</br>'.$paysUser.' : ';
                //J'envoie l'indicatif et le numéro
                echo '+'.$ind.$numFinal. '</p>';
                
                $taille =$bdd->prepare('SELECT taille_numero FROM indicatif where pays = ?');
                $taille-> execute(array($paysUser));
                
                //Ici je récupère dans ma table "indicatif" de la base de données la taille qu'un numéro de téléphone doit faire en fonction du pays.
                while ($donnees = $taille->fetch()){
                    $taille_num=$donnees['taille_numero'];
                    //Si la taille du numéro n'est pas égale à celle qui est envoyée alors on affiche un message pour l'indiquer à l'utilisateur.
                    if(($taille_num) and (strlen($numUser)!=($taille_num))){
                        echo '<p> Attention d\'après <a href="https://fr.wikipedia.org/wiki/Num%C3%A9ro_de_t%C3%A9l%C3%A9phone#Les_structures_des_num%C3%A9ros_de_t%C3%A9l%C3%A9phone_par_pays_(de_A_%C3%A0_Z)">
                        les structures des numéros de téléphone par pays</a> le nombre de chiffres que vous avez indiqué pour ce numéro n\'est pas correct.</p>';

                        
                        echo $taille_num + strlen($ind) .' chiffres sont nécessaires pour ce pays. <small><a href="https://fr.wikipedia.org/wiki/Num%C3%A9ro_de_t%C3%A9l%C3%A9phone#Les_structures_des_num%C3%A9ros_de_t%C3%A9l%C3%A9phone_par_pays_(de_A_%C3%A0_Z) ">Wikipédia</a></small>';
                                                
                    }
                      
                }
                
        ?>  
        
        <form action="/indicatif/indicatif.php">
         <button type="submit"  class="btn btn-secondary ">Retour</button>
      </form>
    <?php endif;?>
        </main>
    
</body>
</html>