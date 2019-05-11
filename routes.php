<?php
$app->group('/v1', function() {

    $this->group('/news', function() {
        $this->get('', '\App\v1\Controllers\NewsController:listNews');
        $this->post('', '\App\v1\Controllers\NewsController:createNews');
        
        $this->get('/{id:[0-9]+}', '\App\v1\Controllers\NewsController:viewNews');
        $this->put('/{id:[0-9]+}', '\App\v1\Controllers\NewsController:updateNews');
        $this->delete('/{id:[0-9]+}', '\App\v1\Controllers\NewsController:deleteNews');
    });

    $this->group('/events', function() {
        $this->get('', '\App\v1\Controllers\EventController:listEvents');
        $this->post('', '\App\v1\Controllers\EventController:createEvent');
        
        $this->get('/{id:[0-9]+}', '\App\v1\Controllers\EventController:viewEvent');
        $this->put('/{id:[0-9]+}', '\App\v1\Controllers\EventController:updateEvent');
        $this->delete('/{id:[0-9]+}', '\App\v1\Controllers\EventController:deleteEvent');
    });

    $this->group('/auth', function() {
        $this->get('/login',\App\v1\Controllers\AuthController::class);
        $this->post('/register', '\App\v1\Controllers\AuthController:createUser');
    });
});