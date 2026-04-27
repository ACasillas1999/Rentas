<div class="form-grid">
    <div>
        <label for="name">Nombre Completo</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required placeholder="Ej. Juan Pérez">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="email">Correo Electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required placeholder="juan@ejemplo.com">
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="password">Contraseña {{ isset($user) ? '(Dejar en blanco para no cambiar)' : '' }}</label>
        <input type="password" id="password" name="password" {{ isset($user) ? '' : 'required' }} minlength="8">
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="password_confirmation">Confirmar Contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>
