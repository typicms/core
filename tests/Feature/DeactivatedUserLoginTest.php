<?php

use Illuminate\Support\Facades\Notification;
use TypiCMS\Modules\Core\Models\User;

beforeEach(function (): void {
    Notification::fake();
});

describe('deactivated user', function (): void {

    test('gets the same response as an activated user but no one-time password', function (): void {
        $user = User::query()->firstOrFail();
        $user->update(['activated' => false]);

        $this->post('/en/otp-login', ['email' => $user->email])
            ->assertRedirect('/en/otp-login-code')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();

        $user->update(['activated' => true]);
    });

    test('cannot submit a one-time password', function (): void {
        $user = User::query()->firstOrFail();
        $user->update(['activated' => false]);

        $this->withSession(['email' => $user->email])
            ->post('/en/otp-login-code', ['one_time_password' => '123456'])
            ->assertSessionHasErrors('one_time_password');

        $this->assertGuest();

        $user->update(['activated' => true]);
    });
});

describe('activated user', function (): void {

    test('can request a one-time password', function (): void {
        $user = User::query()->firstOrFail();
        $user->update(['activated' => true]);

        $this->post('/en/otp-login', ['email' => $user->email])
            ->assertRedirect('/en/otp-login-code')
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, config('one-time-passwords.notification'));
    });

});

describe('unknown email', function (): void {

    test('gets the same response as a registered email', function (): void {
        $this->post('/en/otp-login', ['email' => 'nobody-'.uniqid().'@gmail.com'])
            ->assertRedirect('/en/otp-login-code')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    });

    test('gets the generic code error when submitting a one-time password', function (): void {
        $this->withSession(['email' => 'nobody-'.uniqid().'@gmail.com'])
            ->post('/en/otp-login-code', ['one_time_password' => '123456'])
            ->assertSessionHasErrors('one_time_password');

        $this->assertGuest();
    });

});

describe('registration', function (): void {

    test('with an existing email gets the same response as a new account', function (): void {
        if (! config('typicms.registration.allowed')) {
            $this->markTestSkipped('Registration is disabled.');
        }

        $user = User::query()->firstOrFail();

        $this->post('/en/register', [
            'email' => $user->email,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ])
            ->assertRedirect('/en/otp-login-code')
            ->assertSessionHasNoErrors();

        expect(User::query()->where('email', $user->email)->count())->toBe(1);
    });

});
