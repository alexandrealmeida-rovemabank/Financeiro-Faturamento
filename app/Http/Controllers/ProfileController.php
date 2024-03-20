<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
{
    $user = $request->user(); // Obtém o modelo de usuário do pedido

    // Preenche o modelo de usuário com os dados validados do pedido
    $user->fill($request->validated());

    // Verifica se foi enviada uma nova imagem de perfil
    if ($request->hasFile('imagem_perfil')) {
        $file_name = rand(0, 999999) . '-' . $request->file('imagem_perfil')->getClientOriginalName();
        $path = $request->file('imagem_perfil')->storeAs('uploads', $file_name);

        // Define o caminho da imagem no modelo de usuário
        $user->imagem_perfil = $path;
    }

    // Verifica se o e-mail do usuário foi alterado
    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    // Salva as alterações no modelo de usuário no banco de dados
    $user->save();

    // Redireciona para a página de edição do perfil com uma mensagem de sucesso
    return redirect()->route('profile.edit')->with('status', 'profile-updated');
}


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
