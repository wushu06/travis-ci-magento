var config = {
    deps: [
       // "js/app",
    ],

    paths: {
        'slick' : 'js/slick',
        'viewportChecker': 'js/jquery.viewportchecker.min',
        'selectric' : 'js/jquery.selectric',
    },
    map: {
        '*': {

            'mmenu': 'mmenu/jquery.mmenu.all',
            'mmenuWrapper': 'mmenu/magento/jquery.mmenu.magento'

        }
    },
    shim: {
        "slick" : {
            "deps" : ["jquery"]
        },
        "viewportChecker" : {
            "deps" : ["jquery"]
        },
        "mmenu" : {
            "deps" : ["jquery"]
        },
        "mmenuWrapper" : {
            "deps" : ["jquery", "mmenu"]
        },
        "jquery" : {
            "exports" : 'jQuery'
        }
    }
};
