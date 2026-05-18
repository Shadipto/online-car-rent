<?php

class HomeController extends BaseController
{
    public function index(): void
    {
        $cars = new Car();

        $this->render('home/index', [
            'title' => 'Home',
            'featuredCars' => $cars->getFeatured(),
            'categories' => $cars->getDistinctTypes(),
        ]);
    }
}
