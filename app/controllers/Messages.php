<?php

/*
    MESSAGES CONTROLLER
    Handles messaging/inquiries between users
*/

class Messages extends Controller
{
    private $messageModel;
    private $userModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->messageModel = $this->model('M_Messages');
        $this->userModel = $this->model('M_Users');
    }

    // View inbox
    public function inbox()
    {
        $messages = $this->messageModel->getMessagesByUser($_SESSION['user_id'], 'received');
        $unread_count = $this->messageModel->getUnreadCount($_SESSION['user_id']);

        $data = [
            'messages' => $messages,
            'unread_count' => $unread_count
        ];

        if (isTenant()) {
            $this->view('tenant/v_messages_inbox', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_messages_inbox', $data);
        }
    }

    // View sent messages
    public function sent()
    {
        $messages = $this->messageModel->getMessagesByUser($_SESSION['user_id'], 'sent');

        $data = [
            'messages' => $messages
        ];

        if (isTenant()) {
            $this->view('tenant/v_messages_sent', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_messages_sent', $data);
        }
    }

    // View message thread
    public function view($id)
    {
        $thread = $this->messageModel->getConversationThread($id);

        if (!$thread) {
            flash('message_flash', 'Message not found', 'alert alert-danger');
            redirect('messages/inbox');
        }

        // Check if user has permission to view this message
        $can_view = false;
        foreach ($thread as $msg) {
            if ($msg->sender_id == $_SESSION['user_id'] || $msg->recipient_id == $_SESSION['user_id']) {
                $can_view = true;
                break;
            }
        }

        if (!$can_view) {
            flash('message_flash', 'Unauthorized access', 'alert alert-danger');
            redirect('messages/inbox');
        }

        // Mark messages as read
        foreach ($thread as $msg) {
            if ($msg->recipient_id == $_SESSION['user_id'] && !$msg->is_read) {
                $this->messageModel->markAsRead($msg->id);
            }
        }

        $data = [
            'thread' => $thread,
            'message_id' => $id
        ];

        if (isTenant()) {
            $this->view('tenant/v_message_thread', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_message_thread', $data);
        }
    }

    // Send new message
    public function compose()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $recipient_id = trim($_POST['recipient_id']);
            $property_id = trim($_POST['property_id']) ?: null;
            $subject = trim($_POST['subject']);
            $message = trim($_POST['message']);

            // Validate
            if (empty($recipient_id) || empty($subject) || empty($message)) {
                flash('message_flash', 'Please fill in all fields', 'alert alert-danger');
                redirect('messages/inbox');
            }

            $data = [
                'sender_id' => $_SESSION['user_id'],
                'recipient_id' => $recipient_id,
                'property_id' => $property_id,
                'subject' => $subject,
                'message' => $message,
                'parent_message_id' => null
            ];

            if ($this->messageModel->createMessage($data)) {
                flash('message_flash', 'Message sent successfully', 'alert alert-success');
            } else {
                flash('message_flash', 'Failed to send message', 'alert alert-danger');
            }

            redirect('messages/inbox');
        } else {
            // Show compose form
            if (isTenant()) {
                $this->view('tenant/v_compose_message');
            } else if (isLandlord()) {
                $this->view('landlord/v_compose_message');
            }
        }
    }

    // Reply to message
    public function reply($parent_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $parent_message = $this->messageModel->getMessageById($parent_id);

            if (!$parent_message) {
                flash('message_flash', 'Parent message not found', 'alert alert-danger');
                redirect('messages/inbox');
            }

            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $message = trim($_POST['message']);

            if (empty($message)) {
                flash('message_flash', 'Please enter a message', 'alert alert-danger');
                redirect('messages/view/' . $parent_id);
            }

            // Determine recipient (the other party in the conversation)
            $recipient_id = ($parent_message->sender_id == $_SESSION['user_id'])
                ? $parent_message->recipient_id
                : $parent_message->sender_id;

            $data = [
                'sender_id' => $_SESSION['user_id'],
                'recipient_id' => $recipient_id,
                'property_id' => $parent_message->property_id,
                'subject' => 'Re: ' . $parent_message->subject,
                'message' => $message,
                'parent_message_id' => $parent_id
            ];

            if ($this->messageModel->createMessage($data)) {
                flash('message_flash', 'Reply sent successfully', 'alert alert-success');
            } else {
                flash('message_flash', 'Failed to send reply', 'alert alert-danger');
            }

            redirect('messages/view/' . $parent_id);
        } else {
            redirect('messages/inbox');
        }
    }

    // Mark message as read
    public function markRead($id)
    {
        $this->messageModel->markAsRead($id);
        redirect('messages/view/' . $id);
    }

    // Mark all messages as read
    public function markAllRead()
    {
        $this->messageModel->markAllAsRead($_SESSION['user_id']);
        flash('message_flash', 'All messages marked as read', 'alert alert-success');
        redirect('messages/inbox');
    }

    // Delete message
    public function delete($id)
    {
        if ($this->messageModel->deleteMessage($id, $_SESSION['user_id'])) {
            flash('message_flash', 'Message deleted successfully', 'alert alert-success');
        } else {
            flash('message_flash', 'Failed to delete message', 'alert alert-danger');
        }

        redirect('messages/inbox');
    }

    // Search messages
    public function search()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $search_term = trim($_POST['search_term']);

            $messages = $this->messageModel->searchMessages($_SESSION['user_id'], $search_term);

            $data = [
                'messages' => $messages,
                'search_term' => $search_term
            ];

            if (isTenant()) {
                $this->view('tenant/v_messages_search', $data);
            } else if (isLandlord()) {
                $this->view('landlord/v_messages_search', $data);
            }
        } else {
            redirect('messages/inbox');
        }
    }
}
