# Pre-requisites
##MongoDB 
1. Install MongoDB from here [MongoDB](https://docs.mongodb.com/manual/installation/)  
   $ sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927  
   $ echo "deb http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list  
   $ sudo apt-get update  
   $ sudo apt-get install -y mongodb-org  

## PHP
1. PHP >= 5.5.9
2. penSSL PHP Extension
3. PDO PHP Extension
4. Mbstring PHP Extension
5. Tokenizer PHP Extension
6. Composer - dependency manager for php  see here [Installing Composer](https://getcomposer.org/doc/00-intro.md)
7. MongoDB extension see here [Installing MongoDB ext](http://php.net/manual/en/mongodb.setup.php)  
   $ sudo apt-get install php5-mongo 

##Web Server
1. Install a web server - Apache or Nginx


#Mongo DB Setup
1. $ mongo  
	>use admin  
	>db.createUser({user: "admin", pwd: "pass", roles: ["userAdminAnyDatabase", "dbAdminAnyDatabase", "readWriteAnyDatabase"]})  

	// Change the authSchema to 3 so that you use MONGODB-CR  
	>var schema = db.system.version.findOne({"_id" : "authSchema"})  
	>schema.currentVersion = 3  
	>db.system.version.save(schema)  

	// drop users and create again (WIERD HACK)  
	>db.system.users.remove({})  
	>db.createUser({user: "admin", pwd: "pass", roles: ["userAdminAnyDatabase", "dbAdminAnyDatabase", "readWriteAnyDatabase"]})  
	>exit  

2. $ sudo vi /etc/mongod.conf // to edit this to enable security authorization by adding:

>security:  
>    authorization: enabled  

3. $ sudo service mongod restart // restarting mongo so that it starts with auth enabled  

4. $ mongo  

	>use admin  
	>db.auth("admin", "admin")  
	>use vdb  
	>db.createUser({user: "vuser", pwd: "vpass", roles: [{role: "readWrite", db: "vdb"}]})  
	>exit

5. $ mongo
	>use vdb  
	>db.auth("vuser","vpass")  
	1
	if 1 then all is well

#Application Installation
1. $ cd /home/user/
2. $ git clone https://github.com/CHAIUganda/vl_dashboard.git
3. $ cd vl_dashboard
4. $ composer install
5. $ cp .env.example .env
6. $ vi .env
7.   => Change the LIVE* attributes to correct values so that you pick data from the source  
	 LIVE_HOST2=localhost  
	 LIVE_DATABASE2=vb  
	 LIVE_USERNAME2=user  
	 LIVE_PASSWORD2=secret  
8.   Create the appropriate Mongo database via the mongo db client
9.   => Change the MONGO* attributes to correct values.  
	 MONGO_HOST=localhost  
	 MONGO_DB=vldash  
	 MONGO_USER=xxxx  
	 MONGO_PWD=xxxx  

10. $ php artisan engine:run #This  command runs the api so that data is loaded into mongo db
11. $ php artisan serve --port=nnnn #This command runs the application in development mode, default port is 8000
12. You can now go into your browser and run localhost:nnnn where nnnn is the port number
13. You can run on production by specifying the base of the system at /home/user/vl_dashboard/public

### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
