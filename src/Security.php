<?php 
class Security{
	
    public function __construct($app)
    {
        $this->app = $app;
    }
	
	public function is_user_logged_in()
	{
        return null !== $this->app['session']->get('user');
	}
}
?>