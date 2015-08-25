require.config({
    paths: {
        'jquery': '../bower_components/jquery/dist/jquery',
        'jquery-ui.sortable': '../bower_components/jquery-ui/ui/sortable',
        'jquery-ui.core': '../bower_components/jquery-ui/ui/core',
        'jquery-ui.mouse': '../bower_components/jquery-ui/ui/mouse',
        'jquery-ui.widget': '../bower_components/jquery-ui/ui/widget'
    },
    shim: {
    },
    map: {
        'jquery-ui.sortable': {
            'core': 'jquery-ui.core',
            'mouse': 'jquery-ui.mouse',
            'widget': 'jquery-ui.widget'
        },
        'jquery-ui.mouse': {
            'widget': 'jquery-ui.widget'
        }
    },
    wrap: true,
    findNestedDependencies: true
});