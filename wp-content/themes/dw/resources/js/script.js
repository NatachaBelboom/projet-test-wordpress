class DW_Controller
{
    constructor()
    {
        console.log('ici c\'est le constructeur', document.body);
        // à ce stade ci le dom n'est pas encore pret, car nous sommes dans le
        //permet d'instancier des classes utilitaires par exemple.
    }

    run()
    {
        // désormais, le dom est pret. nous pouvons commencer à le manipuler.
        //Permet d'instancier des classes de composants d'interface par exemple.
        console.log('ici c\'est le run', document.body)
        //this.responsiveMenu = new ResponsiveMenu() ResponsiveMenu est un autre fichier js à importer au debut de ce fichier
    }

}

window.dw = new DW_Controller();
window.addEventListener('load', () => window.dw.run());