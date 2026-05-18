<?php

class MemberApiController extends BaseController
{
    public function destroy(string $id): void
    {
        $this->requireRole('admin', true);
        $this->verifyCsrf(true);

        if (!ctype_digit($id)) {
            $this->json(['success' => false, 'message' => 'Invalid member.'], 422);
        }

        $deleted = (new User())->deleteMember((int) $id);

        if (!$deleted) {
            $this->json(['success' => false, 'message' => 'Member not found or could not be deleted.'], 404);
        }

        $this->json([
            'success' => true,
            'message' => 'Member deleted successfully.',
        ]);
    }
}
