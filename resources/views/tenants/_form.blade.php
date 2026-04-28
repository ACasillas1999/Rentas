<div style="display: grid; gap: 1.5rem;">
    <!-- SECCIÓN 1: IDENTIFICACIÓN -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Identificación del Inquilino
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="full_name">Nombre Completo / Razón Social</label>
                <input id="full_name" name="full_name" value="{{ old('full_name', $tenant->full_name ?? '') }}" placeholder="Nombre completo como aparece en su ID" required>
            </div>
            <div>
                <label for="document_id">RFC / Documento ID</label>
                <input id="document_id" name="document_id" value="{{ old('document_id', $tenant->document_id ?? '') }}" placeholder="Ej. ABCD900101XXX">
            </div>
            <div class="field-span-full">
                <label for="address">Dirección Fiscal / Particular</label>
                <input id="address" name="address" value="{{ old('address', $tenant->address ?? '') }}" placeholder="Calle, número, colonia, ciudad...">
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: CONTACTO -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.18-1.18a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            Información de Contacto
        </h4>
        <div class="form-grid">
            <div>
                <label for="phone">Teléfono de Contacto</label>
                <input id="phone" name="phone" value="{{ old('phone', $tenant->phone ?? '') }}" placeholder="Ej. 3312345678">
            </div>
            <div>
                <label for="email">Correo Electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email', $tenant->email ?? '') }}" placeholder="ejemplo@correo.com">
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: EXPEDIENTE Y NOTAS -->
    <div style="background: var(--surface-soft); padding: 1.2rem; border-radius: 12px; border: 1px solid var(--border);">
        <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
            Expediente Digital
        </h4>
        <div class="form-grid">
            <div class="field-span-full">
                <label for="photo">Fotografía / Identificación</label>
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="file" id="photo" name="photo" accept="image/*" style="padding: 0.5rem 0;">
                    </div>
                    @if(isset($tenant) && $tenant->photo)
                        <div style="flex-shrink: 0;">
                            <img src="{{ asset('storage/' . $tenant->photo) }}" alt="Foto actual" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </div>
                    @endif
                </div>
            </div>
            <div class="field-span-full">
                <label for="notes">Notas / Comentarios sobre el inquilino</label>
                <textarea id="notes" name="notes" placeholder="Antecedentes, referencias, acuerdos especiales..." style="height: 80px;">{{ old('notes', $tenant->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

