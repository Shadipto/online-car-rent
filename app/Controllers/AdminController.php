<?php

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireRole('admin');

        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'counts' => [
                'cars' => (new Car())->countAll(),
                'members' => (new User())->countMembers(),
                'orders' => (new Order())->countAll(),
                'blogs' => (new Blog())->countAll(),
            ],
        ], 'admin');
    }

    public function members(): void
    {
        $this->requireRole('admin');

        $this->render('admin/members', [
            'title' => 'Members',
            'members' => (new User())->getAllMembers(),
        ], 'admin');
    }

    public function orders(): void
    {
        $this->requireRole('admin');

        $this->render('admin/orders', [
            'title' => 'Order History',
            'orders' => (new Order())->getAll(),
            'paymentMethods' => Payment::METHODS,
        ], 'admin');
    }
}
