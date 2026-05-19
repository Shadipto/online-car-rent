<?php

class BlogApiController extends BaseController
{
    private Blog $blogs;

    public function __construct()
    {
        $this->blogs = new Blog();
    }

    public function store(): void
    {
        $this->requireAuth(true);
        $this->verifyCsrf(true);

        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $errors = $this->validatePost($title, $content);

        if ($errors !== []) {
            $this->json([
                'success' => false,
                'message' => reset($errors),
                'errors' => $errors,
            ], 422);
        }

        $postId = $this->blogs->createPost((int) Session::get('user_id'), $title, $content);
        $post = $this->blogs->getById((int) $postId);
        $html = $this->renderPostPartial($post);

        $this->json([
            'success' => true,
            'message' => 'Blog post created successfully.',
            'html' => $html,
        ]);
    }

    public function destroy(string $id): void
    {
        $this->requireAuth(true);
        $this->verifyCsrf(true);

        if (!ctype_digit($id)) {
            $this->json(['success' => false, 'message' => 'Invalid blog post.'], 422);
        }

        $post = $this->blogs->getById((int) $id);

        if ($post === null) {
            $this->json(['success' => false, 'message' => 'Blog post not found.'], 404);
        }

        $isAdmin = AuthMiddleware::isAdmin();
        $isOwner = (int) $post['user_id'] === (int) Session::get('user_id');

        if (!$isAdmin && !$isOwner) {
            $this->json(['success' => false, 'message' => 'You can delete only your own blog posts.'], 403);
        }

        $this->blogs->deletePost((int) $post['id']);

        $this->json([
            'success' => true,
            'message' => 'Blog post deleted successfully.',
        ]);
    }

    private function validatePost(string $title, string $content): array
    {
        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (strlen($title) > 200) {
            $errors['title'] = 'Title must be under 200 characters.';
        }

        if ($content === '') {
            $errors['content'] = 'Content is required.';
        }

        return $errors;
    }

    private function renderPostPartial(array $post): string
    {
        ob_start();
        require app_path('Views/blog/_post.php');

        return ob_get_clean();
    }
}
