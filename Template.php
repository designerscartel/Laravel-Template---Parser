<?php
/**
* Laravel Template Class
*
* Author : Matt Tullett
* Based on the template class by Philip Sturgeon
* license : http://philsturgeon.co.uk/code/dbad-license
*/
class Template 
{

    /**
    * The name of the view.
    *
    * @var string
    */
    public $view;

    /**
    * The view data.
    *
    * @var array
    */
    public $data = array();

    /**
    * The path to the view on disk.
    *
    * @var string
    */
    public $path;

    /**
    * The location of the theme folder on disk.
    *
    * @var string
    */
    public $theme_location;

    /**
    * Holds the theme.
    *
    * @var string
    */
    public $theme = 'main';

    /**
    * Holds the layout.
    *
    * @var string
    */
    public $layout;

    /**
    * The cache content of loaded view files.
    *
    * @var array
    */
    public static $cache = array();

    /**
    * All of the shared view data.
    *
    * @var array
    */
    public static $shared = array();

    /**
    * Holds the parser.
    *
    * @var string
    */
    private $parser;

    /**
    * Holds the parser.
    *
    * @var string
    */
    private $partials;

    /**
    * The Laravel template loader event name.
    *
    * @var string
    */
    const loader = 'laravel.view.loader';


    public function __construct($view, $data = array())
    {
        $this->view = $view;
        $this->data = $data;

        $this->theme_locations = path('public') . 'themes/';
        $this->layout = 'layout';

        $this->path = $this->path($view);

        // If a session driver has been specified, we will bind an instance of the
        // validation error message container to every view. If an error instance
        // exists in the session, we will use that instance.
        if ( ! isset($this->data['errors']))
        {
            if (Session::started() and Session::has('errors'))
            {
                $this->data['errors'] = Session::get('errors');
            }
            else
            {
                $this->data['errors'] = new Laravel\Messages;
            }
        }
    }


    /**
    * Determine if the given view exists.
    *
    * @param  string       $view
    * @param  boolean      $return_path
    * @return string|bool
    */
    public function exists($view, $return_path = false)
    {
        if (starts_with($view, 'name: ') and array_key_exists($name = substr($view, 6), static::$names))
        {
            $view = static::$names[$name];
        }

        $view = $this->theme_locations.'/'.$this->theme.'/views/'.str_replace('.', '/', $view).'.html';
        // We delegate the determination of view paths to the view loader event
        // so that the developer is free to override and manage the loading
        // of views in any way they see fit for their application.

        $path = file_exists($view) ? $view : null;

        if ( ! is_null($path))
        {
            return $return_path ? $path : true;
        }

        return false;
    }


    /**
    * Create a new view instance.
    *
    * <code>
    *      // Create a new view instance
    *      $template = Template::make('home.index');
    *
    *      // Create a new view instance with bound data
    *      $Template = View::make('home.index', array('name' => 'Taylor'));
    * </code>
    *
    * @param  string  $view
    * @param  array   $data
    * @return View
    */
    public static function make($view, $data = array())
    {
        return new static($view, $data);
    }


    /**
    * Get the evaluated string content of the view.
    *
    * @return string
    */
    public function render()
    {
        // Fire off events here if needed

        return $this->get();
    }


    /**
    * Get the evaluated contents of the view.
    *
    * @return string
    */
    public function get()
    {
        $__data = $this->data();

        $this->parser = new Lex\Parser();

        $layout  = $this->path('layouts.layout');

        // add the partials to the data array
        if($this->partials)
        {
            foreach($this->partials as $key => $value)
            {   
                $partial = $this->parser->parse(file_get_contents($value->path), $value->data + $__data);
                $__data['partials'][$key] = $partial;
            }
        }

        // set the body in the system
        $body = $this->parser->parse($this->load(), $__data);
        $__data['body'] = $body;

        // render the main layout
        return $this->parser->parse(file_get_contents($layout), $__data);
    }


    /**
    * Get the path to a given view on disk.
    *
    * @param  string  $view
    * @return string
    */
    protected function path($view)
    {
        if ($path = $this->exists($view,true))
        {
        return $path;
        }

        throw new \Exception("View [$view] doesn't exist.");
    }


    /**
    * Get the array of view data for the view instance.
    *
    * The shared view data will be combined with the view data.
    *
    * @return array
    */
    public function data()
    {
        $data = array_merge($this->data, static::$shared);

        // All nested views and responses are evaluated before the main view.
        // This allows the assets used by nested views to be added to the
        // asset container before the main view is evaluated.
        foreach ($data as $key => $value) 
        {
            if ($value instanceof View or $value instanceof Response)
            {
            $data[$key] = $value->render();
            }
        }

        return $data;
    }


    /**
    * Add a key / value pair to the view data.
    *
    * Bound data will be available to the view as variables.
    *
    * @param  string  $key
    * @param  mixed   $value
    * @return View
    */
    public function with($key, $value = null)
    {
        if (is_array($key))
        {
            $this->data = array_merge($this->data, $key);
        }
        else
        {
            $this->data[$key] = $value;
        }

        return $this;
    }


    /**
    * Add a view instance to the view data.
    *
    * <code>
    *		// Add a view instance to a view's data
    *		$template = Template::make('foo')->partial('footer', 'partials.footer');
    * </code>
    *
    * @param  string  $key
    * @param  string  $view
    * @param  array   $data
    * @return View
    */
    public function partial($key, $view, $data = array())
    {	
        if (is_array($key))
        {
            $this->partials[$key] = array_merge($this->partials[$key], $key);
        }
        else
        {
            $this->partials[$key] = static::make($view, $data);
        }
        return $this;
    }


    /**
    * Get the contents of the view file from disk.
    *
    * @return string
    */
    protected function load()
    {
        if (isset(static::$cache[$this->path]))
        {
            return static::$cache[$this->path];
        }
        else
        { 
            return static::$cache[$this->path] = file_get_contents($this->path);
        }
    }


    /**
    * Get the evaluated string content of the view.
    *
    * @return string
    */
    public function __toString()
    {
        return $this->render();
    }


}

// END Template class