# Pre-requisites
## PHP
1. PHP >= 5.5.9
2. penSSL PHP Extension
3. PDO PHP Extension
4. Mbstring PHP Extension
5. Tokenizer PHP Extension
6. MongoDB extension see here [Installing MongoDB ext](http://php.net/manual/en/mongodb.setup.php)
7. Composer - dependency manager for php  see here [Installing Composer](https://getcomposer.org/doc/00-intro.md) 

##MongoDB 
1. Install MongoDB from here [MongoDB](https://docs.mongodb.com/manual/installation/)

##Web Server
1. Install a web server - Apache or Nginx

#Application Installation
1. $cd /home/user/
2. $git clone https://github.com/CHAIUganda/vl_dashboard.git
3. $cd vl_dashboard
4. $composer install
5. $cp .env.example .env
6. $vi .env
7. => Change the LIVE* attributes to correct values so that you pick data from the source
	LIVE_HOST2=localhost
	LIVE_DATABASE2=vb
	LIVE_USERNAME2=user
	LIVE_PASSWORD2=secret
8. Create the appropriate Mongo database via the mongo db client
9. => Change the MONGO* attributes to correct values. ..For now, ignore the MONGO_USER and MONGO_PWD
	MONGO_HOST=localhost
	MONGO_DB=vldash
	MONGO_USER=xxxx
	MONGO_PWD=xxxx 

10. $php artisan engine:run #This  command runs the api so that data is loaded into mongo db
11. $php artisan serve --port=nnnn #This command runs the application in development mode, default port is 8000
12. You can now go into your browser and run localhost:nnnn where nnnn is the port number
13. You can run on production by specifying the base of the system at /home/user/vl_dashboard/public
### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
