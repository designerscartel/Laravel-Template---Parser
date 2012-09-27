Laravel-Template---Parser
=========================

Laravel Template System and Lex Parser bundle
Based on the template class by Phil Sturgeon

Phil Sturgeons Template library : https://github.com/philsturgeon/codeigniter-template

Lex Parser can be found here : https://github.com/fuelphp/lex

## Starting the bundle in app

	return array(
		'template' => array('auto' => true)
	);


## Example used in a link

	Route::get('theme', function()
	{
		$data = array(
		    	'title'     => 'Lex is Awesome!',
		    	'name'      => 'World',
		    	'real_name' => array(
				'first' => 'Lex',
				'last'  => 'Luther',
		    	),
		    	'hello' => 'page hello!',
		);

		return Template::make('layouts.body', $data)
			// Allows you to overide the main data with partial specifics
			->partial('footer', 'partials.footer', array('hello' => 'footer hello!'));
	});


## To override the config file
	
	Template::location('theme_locations'): 	// Override the locations the themes are stored in
	Template::theme('theme_folder');		// Override the default theme folder
	Template::layout('layout_file');		// Override the default layout file


### Theme folder structure

	> Public
		> themes
			> default
				> assets
				> views
					> layouts
					> partials
			> other
				> assets
				> views
					> layouts
					> partials				