<div class="form-grid">
    <div>
        <label for="full_name">Nombre Completo</label>
        <input id="full_name" name="full_name" value="{{ old('full_name', $tenant->full_name ?? '') }}" required>
    </div>
    <div>
        <label for="document_id">Documento/ID</label>
        <input id="document_id" name="document_id" value="{{ old('document_id', $tenant->document_id ?? '') }}">
    </div>
    <div>
        <label for="phone">Teléfono</label>
        <input id="phone" name="phone" value="{{ old('phone', $tenant->phone ?? '') }}">
    </div>
    <div>
        <label for="email">Correo</label>
        <input id="email" name="email" type="email" value="{{ old('email', $tenant->email ?? '') }}">
    </div>
    <div class="field-span-full">
        <label for="address">Dirección</label>
        <input id="address" name="address" value="{{ old('address', $tenant->address ?? '') }}">
    </div>
    <div class="field-span-full">
        <label for="notes">Notas</label>
        <textarea id="notes" name="notes">{{ old('notes', $tenant->notes ?? '') }}</textarea>
    </div>
    <div class="field-span-full">
        <label for="photo">Foto del Inquilino</label>
        <input type="file" id="photo" name="photo" accept="image/*">
        @if(isset($tenant) && $tenant->photo)
            <div style="margin-top: 0.5rem;">
                <img src="{{ asset('storage/' . $tenant->photo) }}" alt="Foto actual" style="max-width: 200px; border-radius: 8px;">
            </div>
        @endif
    </div>
</div>

