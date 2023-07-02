//Faire une fonction pour que lorsque la souris survole le choix d'année il affiche le choix avec Ctrl
function afficher_aide(){
    var conseil = document.getElementById("aide");
    if(conseil.style.display == "none"){
        conseil.style.display = "block";
    }
    else conseil.style.display = "none";
}