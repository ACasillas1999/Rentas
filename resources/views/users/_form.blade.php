{{-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 1: Datos de la Cuenta
═══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div>
        <label for="name">Nombre Completo</label>
        <input type="text" id="name" name="name"
               value="{{ old('name', $user->name ?? '') }}"
               required placeholder="Ej. Juan Pérez" autocomplete="name">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="email">Correo Electrónico</label>
        <input type="email" id="email" name="email"
               value="{{ old('email', $user->email ?? '') }}"
               required placeholder="juan@ejemplo.com" autocomplete="email">
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="password">Contraseña {{ isset($user) ? '(dejar en blanco para no cambiar)' : '' }}</label>
        <input type="password" id="password" name="password"
               {{ isset($user) ? '' : 'required' }} minlength="8"
               placeholder="{{ isset($user) ? '••••••••' : 'Mínimo 8 caracteres' }}"
               autocomplete="{{ isset($user) ? 'current-password' : 'new-password' }}">
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="password_confirmation">Confirmar Contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               {{ isset($user) ? '' : 'required' }}
               placeholder="Repetir contraseña" autocomplete="new-password">
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 2: Rol del Usuario
═══════════════════════════════════════════════════════════════ --}}
<div style="margin-top:1.5rem;">
    <label style="font-weight:700;font-size:0.88rem;text-transform:uppercase;letter-spacing:.5px;color:#384658;margin-bottom:.75rem;display:block;">
        Rol del Usuario
    </label>

    @php
        $currentRole = old('role', $user->role ?? 'viewer');
        $roles = [
            'admin'   => ['icon' => '🛡️', 'label' => 'Admin',   'desc' => 'Acceso total al sistema, sin restricciones.'],
            'manager' => ['icon' => '⚙️', 'label' => 'Manager', 'desc' => 'Gestión operativa completa; se le pueden restringir propiedades.'],
            'viewer'  => ['icon' => '👁️', 'label' => 'Viewer',  'desc' => 'Solo lectura. No puede crear, editar ni eliminar.'],
        ];
    @endphp

    <div id="role-cards" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
        @foreach($roles as $value => $info)
            <label for="role_{{ $value }}"
                   style="display:flex;flex-direction:column;gap:.35rem;padding:.9rem 1rem;border:2px solid {{ $currentRole === $value ? '#4a90d9' : '#e2e8f0' }};border-radius:12px;cursor:pointer;background:{{ $currentRole === $value ? 'rgba(74,144,217,.06)' : '#fff' }};transition:.15s;"
                   class="role-card-label" data-value="{{ $value }}">
                <input type="radio" name="role" id="role_{{ $value }}" value="{{ $value }}"
                       {{ $currentRole === $value ? 'checked' : '' }}
                       style="display:none;" required>
                <span style="font-size:1.4rem;">{{ $info['icon'] }}</span>
                <strong style="font-size:.95rem;color:#1a2e4a;">{{ $info['label'] }}</strong>
                <small style="color:#64748b;font-size:.78rem;line-height:1.4;">{{ $info['desc'] }}</small>
            </label>
        @endforeach
    </div>
    @error('role') <small class="text-danger">{{ $message }}</small> @enderror
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 3 + 4: Permisos y Propiedades (oculto para admin)
═══════════════════════════════════════════════════════════════ --}}
<div id="advanced-section" style="margin-top:1.5rem;display:{{ $currentRole === 'admin' ? 'none' : 'block' }};">

    {{-- Separador visual --}}
    <div style="border-top:1px dashed #d1dae8;margin-bottom:1.5rem;"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        {{-- PERMISOS PERSONALIZADOS --}}
        <div>
            <label style="font-weight:700;font-size:0.88rem;text-transform:uppercase;letter-spacing:.5px;color:#384658;margin-bottom:.2rem;display:block;">
                Permisos Personalizados
            </label>
            <p style="font-size:.78rem;color:#64748b;margin:0 0 .75rem;">
                Sobrescriben los permisos del rol. Si no marcas ninguno, se usan los del rol.
            </p>

            @php $currentPerms = old('permissions', $userPermissions ?? []); @endphp

            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($allPermissions as $module => $perms)
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                        <div style="padding:.45rem .75rem;background:#eef2f7;font-size:.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.4px;">
                            {{ $moduleLabels[$module] ?? ucfirst($module) }}
                        </div>
                        <div style="padding:.4rem .75rem;display:flex;flex-wrap:wrap;gap:.3rem .9rem;">
                            @foreach($perms as $perm)
                                <label style="display:flex;align-items:center;gap:.35rem;font-size:.82rem;color:#334155;cursor:pointer;white-space:nowrap;">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                           {{ in_array($perm, $currentPerms) ? 'checked' : '' }}
                                           style="accent-color:#4a90d9;width:14px;height:14px;">
                                    {{ $permissionLabels[$perm] ?? $perm }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ACCESO A PROPIEDADES --}}
        <div>
            <label style="font-weight:700;font-size:0.88rem;text-transform:uppercase;letter-spacing:.5px;color:#384658;margin-bottom:.2rem;display:block;">
                Acceso a Propiedades
            </label>
            <p style="font-size:.78rem;color:#64748b;margin:0 0 .75rem;">
                Si no seleccionas ninguna, el usuario verá <strong>todas</strong>.
                Si seleccionas una o más, solo verá datos de esas propiedades.
            </p>

            @php $currentProps = old('allowed_properties', $userPropertyIds ?? []); @endphp

            @if($properties->isEmpty())
                <div style="padding:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:.83rem;color:#92400e;">
                    ⚠️ No hay propiedades registradas aún.
                </div>
            @else
                {{-- Botones de selección rápida --}}
                <div style="display:flex;gap:.4rem;margin-bottom:.5rem;">
                    <button type="button" onclick="toggleAllProps(true)"
                            style="font-size:.75rem;padding:.25rem .6rem;border:1px solid #d1dae8;border-radius:6px;background:#fff;color:#475569;cursor:pointer;">
                        ✓ Todas
                    </button>
                    <button type="button" onclick="toggleAllProps(false)"
                            style="font-size:.75rem;padding:.25rem .6rem;border:1px solid #d1dae8;border-radius:6px;background:#fff;color:#475569;cursor:pointer;">
                        ✗ Ninguna
                    </button>
                </div>

                <div style="display:flex;flex-direction:column;gap:.35rem;max-height:260px;overflow-y:auto;padding-right:.25rem;">
                    @foreach($properties as $property)
                        <label style="display:flex;align-items:center;gap:.6rem;padding:.45rem .7rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:.84rem;color:#334155;transition:.12s;"
                               class="property-label">
                            <input type="checkbox" name="allowed_properties[]"
                                   value="{{ $property->id }}" class="prop-check"
                                   {{ in_array($property->id, $currentProps) ? 'checked' : '' }}
                                   style="accent-color:#4a90d9;width:15px;height:15px;flex-shrink:0;">
                            <span style="flex:1;min-width:0;">
                                <strong style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $property->name }}</strong>
                                @if($property->city)
                                    <small style="color:#94a3b8;">{{ $property->city }}</small>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

{{-- ═══ Scripts ═══ --}}
<script>
(function() {
    // Role card selection styling
    document.querySelectorAll('input[name="role"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.role-card-label').forEach(function(lbl) {
                lbl.style.borderColor = '#e2e8f0';
                lbl.style.background  = '#fff';
            });
            var selected = document.querySelector('.role-card-label[data-value="' + radio.value + '"]');
            if (selected) {
                selected.style.borderColor = '#4a90d9';
                selected.style.background  = 'rgba(74,144,217,.06)';
            }
            document.getElementById('advanced-section').style.display =
                radio.value === 'admin' ? 'none' : 'block';
        });
    });

    // Highlight checked property rows
    document.querySelectorAll('.prop-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            cb.closest('.property-label').style.borderColor = cb.checked ? '#4a90d9' : '#e2e8f0';
            cb.closest('.property-label').style.background  = cb.checked ? 'rgba(74,144,217,.05)' : '#f8fafc';
        });
        // Initial highlight
        if (cb.checked) {
            cb.closest('.property-label').style.borderColor = '#4a90d9';
            cb.closest('.property-label').style.background  = 'rgba(74,144,217,.05)';
        }
    });
})();

function toggleAllProps(select) {
    document.querySelectorAll('.prop-check').forEach(function(cb) {
        cb.checked = select;
        cb.closest('.property-label').style.borderColor = select ? '#4a90d9' : '#e2e8f0';
        cb.closest('.property-label').style.background  = select ? 'rgba(74,144,217,.05)' : '#f8fafc';
    });
}
</script>
