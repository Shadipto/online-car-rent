<?php

class BlogController extends BaseController
{
    public function index(): void
    {
        $this->render('blog/index', [
            'title' => 'Blog',
            'posts' => (new Blog())->getAll(),
            'errors' => [],
            'old' => [],
        ]);
    }
}
