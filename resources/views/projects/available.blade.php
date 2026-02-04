@extends('admin.layouts.app')

@section('title', 'Proyectos Disponibles - Devil Panels')
@section('page_id', 'projects')

@section('content')
    <x-session-alerts />
    <div class="card">
        <div class="card-header">
            <h5>
                <i class="fas fa-book-skull"></i>Scams Disponibles
                <span class="badge badge-success">{{ $projects->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="projects-grid">
                @forelse($projects as $project)
                    <div class="project-card card" data-project-id="{{ $project->id }}">
                        <div class="card-header">
                            <h5><i class="fas fa-project-diagram"></i> {{ $project->name }}</h5>
                            @if($project->is_active)
                                <span class="pill success"><i class="fas fa-circle"></i> Activo</span>
                            @else
                                <span class="pill error"><i class="fas fa-circle"></i> Inactivo</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="project-content">
                                <div class="project-info">
                                    <p class="project-description">
                                        {{ $project->description ?: 'Este proyecto no tiene descripcion aun.' }}
                                    </p>
                                </div>
                                <div class="project-logo">
                                    @if($project->logo_url)
                                        <img src="{{ $project->logo_url }}" alt="{{ $project->name }}">
                                    @else
                                        <div class="logo-placeholder">
                                            <i class="fas fa-project-diagram"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-block btn-view-details"
                                data-project='@json($project)'>
                                <i class="fas fa-eye"></i> Ver Detalles
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            No hay scams disponibles en este momento.
                            <a href="{{ route('projects.my') }}">Ver mis scams</a>.
                        </div>
                    </div>
                @endforelse
            </div>
            <x-pagination :paginator="$projects" />
        </div>
    </div>

    {{-- Modal de detalles del proyecto --}}
    <div class="modalOverlay" id="projectModalOverlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="projectModalTitle">
            <div class="modalHeader">
                <div class="title" id="projectModalTitle">
                    <i class="fas fa-project-diagram"></i>
                    <span id="modalProjectName">—</span>
                </div>
                <button class="iconBtn" id="closeProjectModal" aria-label="Cerrar">✕</button>
            </div>

            <div class="modalBody">
                <div class="modalBodyFlex">
                    <div class="modalBodyLeft">
                        <div class="pill" id="modalStatusPill">Activo</div>
                    </div>
                </div>

                <div class="project-modal-content">
                    <div class="project-modal-logo" id="modalProjectLogo">
                        <div class="logo-placeholder large">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>

                    <div class="project-modal-details">
                        <div class="detail-section">
                            <h4 class="content-label"><i class="fas fa-info-circle"></i> Descripcion</h4>
                            <p id="modalProjectDescription">—</p>
                        </div>

                        <div class="detail-section">
                            <h4 class="content-label"><i class="fas fa-link"></i> URL del Proyecto</h4>
                            <a href="#" id="modalProjectUrl" target="_blank" class="project-url-link">—</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modalActions">
                <button type="button" class="btn btn-secondary" id="btnCloseModal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnSubscribe" data-project-id="">
                    <i class="fas fa-paper-plane"></i> Subscribirse
                </button>
            </div>
        </div>
    </div>
@endsection

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
    <style>
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .empty-state {
            grid-column: 1 / -1;
        }

        .project-card {
            display: flex;
            flex-direction: column;
        }

        .project-card .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .project-card .card-header h5 {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .project-card .card-header .pill {
            flex-shrink: 0;
            font-size: 10px;
            padding: 4px 10px;
        }

        .project-card .card-header .pill i {
            font-size: 6px;
            margin-right: 5px;
        }

        .project-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .project-content {
            display: flex;
            gap: 16px;
            flex: 1;
        }

        .project-info {
            flex: 1;
            min-width: 0;
        }

        .project-description {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-logo {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
        }

        .project-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
        }

        .logo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 28px;
        }

        .logo-placeholder.large {
            width: 120px;
            height: 120px;
            font-size: 40px;
        }

        .btn[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modalOverlay {
            display: none;
        }

        .modalOverlay.open {
            display: flex;
        }

        .project-modal-content {
            display: flex;
            gap: 24px;
            padding: 16px 0;
        }

        .project-modal-logo {
            flex-shrink: 0;
        }

        .project-modal-logo img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.05);
        }

        .project-modal-details {
            flex: 1;
            min-width: 0;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .detail-section h4.content-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .detail-section h4.content-label i {
            font-size: 11px;
        }

        .detail-section p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .project-url-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--devil-red-light, #ff6b6b);
            font-size: 13px;
            text-decoration: none;
            word-break: break-all;
        }

        .project-url-link:hover {
            text-decoration: underline;
        }

        .btn-subscribed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1)) !important;
            border-color: rgba(34, 197, 94, 0.3) !important;
            color: #22c55e !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        @media (max-width: 520px) {
            .projects-grid {
                grid-template-columns: 1fr;
            }

            .project-content {
                flex-direction: column-reverse;
                align-items: center;
                text-align: center;
            }

            .project-logo {
                width: 60px;
                height: 60px;
            }

            .project-modal-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .detail-section h4.content-label {
                justify-content: center;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
(function() {
    const modal = document.getElementById('projectModalOverlay');
    const closeBtn = document.getElementById('closeProjectModal');
    const closeBtnFooter = document.getElementById('btnCloseModal');
    const subscribeBtn = document.getElementById('btnSubscribe');
    let currentProjectId = null;
    let lockoutTimer = null;

    // Abrir modal al hacer clic en "Ver Detalles"
    document.querySelectorAll('.btn-view-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const project = JSON.parse(this.dataset.project);
            openProjectModal(project);
        });
    });

    function openProjectModal(project) {
        currentProjectId = project.id;

        // Actualizar contenido del modal
        document.getElementById('modalProjectName').textContent = project.name;

        // Status pill
        const statusPill = document.getElementById('modalStatusPill');
        if (project.is_active) {
            statusPill.className = 'pill success';
            statusPill.innerHTML = '<i class="fas fa-circle" style="font-size:6px;margin-right:5px;"></i> Activo';
        } else {
            statusPill.className = 'pill error';
            statusPill.innerHTML = '<i class="fas fa-circle" style="font-size:6px;margin-right:5px;"></i> Inactivo';
        }

        // Logo
        const logoContainer = document.getElementById('modalProjectLogo');
        if (project.logo_url) {
            logoContainer.innerHTML = `<img src="${project.logo_url}" alt="${project.name}">`;
        } else {
            logoContainer.innerHTML = `<div class="logo-placeholder large"><i class="fas fa-project-diagram"></i></div>`;
        }

        // Descripcion
        document.getElementById('modalProjectDescription').textContent =
            project.description || 'Este proyecto no tiene descripcion aun.';

        // URL
        const urlLink = document.getElementById('modalProjectUrl');
        if (project.url) {
            urlLink.href = project.url;
            urlLink.textContent = project.url;
            urlLink.style.display = 'inline-flex';
        } else {
            urlLink.style.display = 'none';
        }

        // Boton subscribe
        subscribeBtn.dataset.projectId = project.id;
        subscribeBtn.disabled = false;
        subscribeBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribirse';
        subscribeBtn.classList.remove('btn-subscribed');

        // Verificar rate limit
        if (window.isRateLimited && window.isRateLimited()) {
            updateSubscribeButtonCountdown();
        }

        // Mostrar modal
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        currentProjectId = null;
    }

    // Event listeners para cerrar modal
    closeBtn.addEventListener('click', closeModal);
    closeBtnFooter.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    // Subscribirse
    subscribeBtn.addEventListener('click', async function() {
        if (this.disabled) return;

        // Verificar rate limit
        if (window.isRateLimited && window.isRateLimited()) {
            const remaining = Math.ceil(window.getRateLimitRemaining() / 1000);
            if (window.Toast) {
                Toast.show({
                    type: 'warning',
                    title: 'Espera',
                    message: `Debes esperar ${remaining} segundos mas`,
                    duration: 3000
                });
            }
            return;
        }

        const projectId = this.dataset.projectId;
        const originalHTML = this.innerHTML;

        // Estado de loading
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        try {
            const response = await fetch(`{{ url('projects') }}/${projectId}/request`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 429) {
                // Rate limit - el interceptor global maneja el toast
                disableSubscribeButton();
                return;
            }

            if (response.ok) {
                if (window.Toast) {
                    Toast.show({
                        type: 'success',
                        title: 'Solicitud enviada',
                        message: 'Tu solicitud de acceso ha sido enviada correctamente',
                        duration: 3000
                    });
                }

                // Cambiar boton a "Subscrito"
                this.innerHTML = '<i class="fas fa-check"></i> Solicitud Enviada';
                this.classList.add('btn-subscribed');
                this.disabled = true;

                // Remover el card de la lista despues de un momento
                setTimeout(() => {
                    const card = document.querySelector(`.project-card[data-project-id="${projectId}"]`);
                    if (card) {
                        card.style.animation = 'fadeOut 0.3s ease forwards';
                        setTimeout(() => card.remove(), 300);
                    }
                    closeModal();
                }, 1500);
            } else {
                const data = await response.json().catch(() => ({}));
                if (window.Toast) {
                    Toast.show({
                        type: 'error',
                        title: 'Error',
                        message: data.message || 'Ocurrio un error al procesar la solicitud',
                        duration: 5000
                    });
                }
                this.disabled = false;
                this.innerHTML = originalHTML;
            }
        } catch (error) {
            if (window.Toast) {
                Toast.show({
                    type: 'error',
                    title: 'Error de conexion',
                    message: 'No se pudo conectar con el servidor',
                    duration: 5000
                });
            }
            this.disabled = false;
            this.innerHTML = originalHTML;
        }
    });

    function disableSubscribeButton() {
        subscribeBtn.disabled = true;
        updateSubscribeButtonCountdown();
    }

    function updateSubscribeButtonCountdown() {
        if (lockoutTimer) clearInterval(lockoutTimer);

        lockoutTimer = setInterval(() => {
            const remaining = window.getRateLimitRemaining ? window.getRateLimitRemaining() : 0;

            if (remaining <= 0) {
                clearInterval(lockoutTimer);
                subscribeBtn.disabled = false;
                subscribeBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribirse';
                if (window.Toast) {
                    Toast.show({
                        type: 'info',
                        title: 'Listo',
                        message: 'Ya puedes realizar solicitudes nuevamente',
                        duration: 3000
                    });
                }
                return;
            }

            const seconds = Math.ceil(remaining / 1000);
            subscribeBtn.innerHTML = `<i class="fas fa-clock"></i> Espera ${seconds}s`;
        }, 1000);
    }

    // Escuchar evento de rate limit global
    window.addEventListener('rateLimitError', () => {
        if (modal.classList.contains('open')) {
            disableSubscribeButton();
        }
    });

    // Animacion para fadeOut
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
    `;
    document.head.appendChild(style);
})();
</script>
@endpush
