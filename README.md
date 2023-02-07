

Change log: 

# Remove redundant autoload.
Composer is suppose to handle that.
What I did was to add:

```
"autoload": {
       "classmap": ["oclasses/"]
   },
```

and then you need to run: 

```$ composer dump-autoload```

You can use the PSR-4 standard where the namespace follows the directory structure, 
but as oclasses doesn't follow any standard, just add the classmap section, every time
you run the 'composer dumpo-autoload' it will scan the directory and add the proper
load data to a file called autoload_classmap.php on the vendor/composer directory. 

