# Introduction

This is a sample project that uses Slim Framework 3 with basic authentication and with JWT. This project aims to present folder structure and form authentication using Middlewares and the Slim framework to build an API.

This project uses the following stacks:

- Php 7.2
- Slim FrameWork 3
- Composer (To install dependencies)
- PDO (Using sqlite in this project)
- Doctrine 2
- Monolog

# Installation

You must have PHP 7.2 and the composer installation you can follow in this [link](https://getcomposer.org/doc/00-intro.md)

In case of using linux execute the commands:

```
curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.bashrc
```
Create a folder at the root of the project named log.

```
mkdir log
```

After that we need to install our dependencies.

Run the command:

```
composer install -vv
```
After that install all the dependencies, in case of a bug in the dependencies, evaluate if installed all dependencies of PHP to run, generally give error by the lack of installation of php7.2-zip, php7.2-mbstring and php7.2-dom .


With everything installed, run the following command:

```
vendor/bin/doctrine orm:schema-tool:update --force
```

This will create the database and the initial tables. If you want to use another database, change the configuration in the db-config.php file

To test if everything is ok, run the command:

```
php -S localhost:8000
```

Make sure that the following address is returned empty and no errors in the console:

```
http://localhost:8000/v1/news
```

If it's okay, let's go to the next step.

# Creating user

Now we must create our user so we can create news and events.

Run the command via curl:
```
curl -X POST http://localhost:8000/v1/auth/register -H "Content-type: application/json" -d '{"username":"test","password":"123"}' -i
```

If run without error, it will return your JWT token, something similar to this:
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NTc2MDk4MDQsInVzZXIiOiJ0ZXN0ZSIsImlkIjoxfQ
```

After insertion, check if you are able to log in:

```
curl  -u teste:"123" -X GET -H "Content-type: application/json" http://localhost:8000/v1/auth/login  -i
```

If you return the token and no error, authentication was successful.

# Creating News and Events

Let's create some news and then some events. Only news can be viewed without the need for authentication, however, authentication is required to be viewed.

To create a news run:

```
curl -X POST -H "X-Token:<token-here>" -H "Content-type: application/json" http://localhost:8000/v1/news -d '{"title":"Title here to the News", "description":" Describe your news here"}' -i
```

The parameters that can be sent, to create news, are:

- Title (Required)
- Description
- Status(Boolean)

To create an event run:

```
curl -X POST -H "X-Token:<token-here>" -H "Content-type: application/json" http://localhost:8000/v1/events -d '{"title":"Evento XPTO", "description":"Description of the event", "publish_date":"2019-05-10 15:30:00"}' -i
```

The parameters that can be sent, to create an event, are:

- Title (Required)
- Description
- Publish Date (Required)
- Status(Boolean)

After that we will try to visualize the news:

```
curl -X GET  -H "Content-type: application/json" http://localhost:8000/v1/news -i
```


```
curl -X GET  -H "Content-type: application/json" http://localhost:8000/v1/news/1 -i
```

It will return the news list in JSON format.

To view the events:

```
curl -X GET -H "X-Token:<token-here>" -H "Content-type: application/json" http://localhost:8000/v1/event -i
```


# Updating/deleting events and news

For both news and events, you'll make a PUT with a specific id of the object to update and pass a body as well.

Let's update the news with id 1:

```
curl -X PUT -H "X-Token:<token-here>" -H "Content-type: application/json" http://localhost:8000/v1/news/1 -d '{"title":"Another title", "description":"Another description","status":"false"}' -i
```

The same goes for an event.

To delete a news:

```
curl -X DELETE -H "X-Token:<token-here>" -H "Content-type: application/json" http://localhost:8000/v1/news/1 -i
```

# Cors

This project is using manual application to release Access-Control-Allow-Origin and some headers like "X-Token" to execute access.

## If you are using an Apache / Nginx, do not forget to set the rules to free the headers and also comment the middleware in the bootstrap.php file that enables the headers named \Tuupola\Middleware\CorsMiddleware