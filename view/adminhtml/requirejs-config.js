var config = {
    map: {
        '*': {
            myjs: 'TM_AndroidServices/js/allgrids',
            //dropdown: 'TM_AndroidServices/js/dropdown.min',
            //dropdowncss: 'TM_AndroidServices/css/jquery.dropdown',
        }
    },
    paths: {            
        'dropdown': 'TM_AndroidServices/js/dropdown.min',
    },   
    shim: {
    'dropdown': {
        deps: ['jquery']
    },
  }
};