<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;

class Toaster extends Component
{
    private const TYPES = ['success', 'error', 'warning', 'info'];

    /**
     * Messages to show on page load: the explicit message prop when given,
     * otherwise whatever a controller flashed to the session
     * (success/error/warning/info/status) and validation errors from the default bag.
     */
    public array $toasts = [];

    public function __construct(public ?string $message = null, public string $type = 'success')
    {
        $type = in_array($type, self::TYPES, true) ? $type : 'info';

        if ($message !== null && $message !== '') {
            $this->toasts[] = ['type' => $type, 'message' => $message];

            return;
        }

        foreach (['success', 'error', 'warning', 'info'] as $key) {
            $this->addFlash($key, $key);
        }
        $this->addFlash('status', 'success');

        if (session('resent')) {
            $this->toasts[] = ['type' => 'success', 'message' => 'A fresh verification link has been sent to your email address.'];
        }

        $errors = session('errors');
        if ($errors instanceof ViewErrorBag && $errors->any()) {
            $messages = array_values(array_unique($errors->all()));
            $this->toasts[] = [
                'type' => 'error',
                'message' => array_slice($messages, 0, 5),
            ];
        }
    }

    private function addFlash(string $sessionKey, string $type): void
    {
        $value = session($sessionKey);

        if (is_string($value) && $value !== '') {
            $this->toasts[] = ['type' => $type, 'message' => $value];
        }
    }

    public function render(): View
    {
        return view('components.toaster');
    }
}
