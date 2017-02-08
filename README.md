# Config
Examples:

    // Create empty config
    $config = new Config(null);
    $config[] = 1;
    $config[] = 2;
    print_r(count($config)); // 2


    $config = new Config(null, ['username' => 'user', 'password' => 'pass123']);
    print_r(count($config)); // 
		/*
		Config Object
		(
		    [_configValues:protected] => Array
		        (
		            [username] => user
		            [password] => pass123
		        )
		
		)
		*/
