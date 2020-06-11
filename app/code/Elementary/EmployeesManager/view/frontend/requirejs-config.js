
var config = {
	map: {
		'*': {
			'formAjax': 'Elementary_EmployeesManager/js/ajax-call'
		}
	},
    paths: {
	    'manager'  : 'Elementary_EmployeesManager/js/manager',
	    'form'  : 'Elementary_EmployeesManager/js/form',
		'pagination' : 'Elementary_EmployeesManager/js/pagination.min'
    },
	shim: {
		'manager': {
			deps: ['jquery']
		},
		'pagination': {
			deps: ['jquery']
		}
	}
};
