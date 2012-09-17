Laravel-Template---Parser
=========================

Laravel Template System and Lex Parser bundle
Based on the template class by Philip Sturgeon


## Exmaple used in a link

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
			->partial('footer', 'partials.footer', array('hello' => 'footer hello!'));
	});



### Theme folder structure

	> Public
		> themes
			> assets
			> views
				> layouts
				> partials
