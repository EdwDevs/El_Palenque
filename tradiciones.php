<?php
// Iniciar o reanudar la sesión
session_start();

// Validar la sesión sin mostrar información
$sesion_activa = (session_status() == PHP_SESSION_ACTIVE);
$sesion_con_datos = !empty($_SESSION);
$usuario_logueado = isset($_SESSION['usuario']);
$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin';

// Puedes usar estas variables para lógica condicional en tu página
// pero no se mostrará ninguna información de depuración

// Si necesitas verificar que todo funciona, puedes usar este comentario
/*
$info_debug = [
    'sesion_activa' => $sesion_activa,
    'sesion_con_datos' => $sesion_con_datos,
    'usuario_logueado' => $usuario_logueado,
    'es_admin' => $es_admin,
    'session_data' => $_SESSION
];
*/

// No hay salida visible en la página
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="San Basilio de Palenque, tradiciones, música, Lumbalú, medicina tradicional, patrimonio cultural">
    <meta name="author" content="San Basilio de Palenque">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Preconectar a dominios externos para mejorar rendimiento -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://images.unsplash.com">
    
    <!-- Enlace a Bootstrap para estilos responsivos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS para animaciones de scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Enlace a la hoja de estilos principal -->
    <link rel="stylesheet" href="styles.css">
    <!-- Estilos específicos para la página de tradiciones -->
    <link rel="stylesheet" href="css/tradiciones.css">
</head>
<body>
    <!-- Encabezado de la página usando los estilos de styles.css -->
    <header class="fixed-top">
    <nav class="navbar navbar-expand-lg" aria-label="Navegación principal">
        <div class="container-fluid">
            <div class="header-logo">
                <a href="index.php" aria-label="Ir a la página de inicio">
                    <img src="palenque.jpeg" alt="San Basilio de Palenque" width="120" height="120"> 
                </a>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                    aria-controls="navbarMain" aria-expanded="false" aria-label="Mostrar menú de navegación">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse nav-links" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="tradiciones.php" aria-current="page">Tradiciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="productos.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historias.php">Historias</a>
                    </li>
                    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link admin-link" href="admin_home.php">
                            <i class="fas fa-tachometer-alt"></i> Panel Admin
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="auth-section ms-auto">
                    <?php if(isset($_SESSION['usuario'])): ?>
                        <div class="user-welcome">
                            <span class="user-name">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                            </span>
                            <a href="logout.php" class="logout-btn" aria-label="Cerrar sesión">
                                <i class="fas fa-sign-out-alt"></i> Salir
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="login-btn" aria-label="Iniciar sesión">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                            <a href="register.php" class="register-btn" aria-label="Registrarse">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<style>
/* Estilos para el header y la navegación */
.fixed-top {
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.navbar {
    padding: 0.5rem 1rem;
}

.header-logo img {
    transition: transform 0.3s ease;
}

.header-logo img:hover {
    transform: scale(1.05);
}

.nav-link {
    font-weight: 500;
    color: #495057;
    margin: 0 5px;
    padding: 8px 15px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #212529;
}

.nav-link.active {
    color: #fff;
    background-color: #6c757d;
}

.admin-link {
    color: #28a745;
    border: 1px dashed #28a745;
}

.admin-link:hover {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

/* Estilos para la sección de autenticación */
.auth-section {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;
}

.user-welcome {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    padding: 8px 15px;
    border-radius: 30px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.user-name {
    font-weight: 600;
    color: #343a40;
    margin-right: 15px;
    display: flex;
    align-items: center;
}

.user-name i {
    font-size: 1.2rem;
    margin-right: 8px;
    color: #6c757d;
}

.logout-btn {
    background-color: #dc3545;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.logout-btn:hover {
    background-color: #c82333;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.logout-btn i {
    margin-right: 5px;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}

.login-btn, .register-btn {
    padding: 7px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.login-btn {
    background-color: transparent;
    color: #007bff;
    border: 1px solid #007bff;
}

.login-btn:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
    text-decoration: none;
}

.register-btn {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
}

.register-btn:hover {
    background-color: #0069d9;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.login-btn i, .register-btn i {
    margin-right: 5px;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .auth-section {
        margin-top: 15px;
        justify-content: center;
        width: 100%;
    }
    
    .user-welcome, .auth-buttons {
        width: 100%;
        justify-content: center;
    }
}
</style>

    <main id="contenido-principal">
        <!-- Banner principal con título y descripción -->
        <section class="tradition-banner" aria-labelledby="banner-title">
            <div class="container">
                <h1 id="banner-title" data-aos="fade-up">San Basilio de Palenque</h1>
                <p data-aos="fade-up" data-aos-delay="200">Música, Rituales y Medicina Tradicional en el Primer Pueblo Libre de América</p>
            </div>
        </section>

        <!-- Introducción -->
        <section class="tradition-section" aria-labelledby="intro-title">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto" data-aos="fade-up">
                        <h2 id="intro-title" class="visually-hidden">Introducción</h2>
                        <p class="lead">San Basilio de Palenque, corregimiento de Mahates en el departamento de Bolívar, Colombia, se erige como un faro de resistencia cultural y autonomía. Declarado Patrimonio Inmaterial de la Humanidad por la UNESCO en 2005, este pueblo fundado por cimarrones africanos en el siglo XVII preserva prácticas ancestrales que fusionan raíces bantúes con adaptaciones locales.</p>
                        
                        <p>Su música, ritos funerarios y sistemas médicos tradicionales no solo reflejan una herencia africana vibrante, sino también procesos de adaptación y resistencia que han definido su identidad. Este informe explora tres pilares fundamentales de su cultura: las expresiones musicales desde los tambores tradicionales hasta fusiones contemporáneas, el ritual del Lumbalú como eje de su cosmovisión, y el uso de plantas medicinales basado en una clasificación térmica única.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contexto Histórico y Cultural -->
        <section class="tradition-section" id="contexto" aria-labelledby="contexto-title">
            <div class="container">
                <h2 id="contexto-title" data-aos="fade-right">Contexto Histórico y Cultural</h2>
                
                <div class="row">
                    <div class="col-md-6" data-aos="fade-up">
                        <div class="info-card">
                            <h3>Orígenes y Significado como Primer Territorio Libre</h3>
                            <p>Fundado en el siglo XVII por esclavizados fugitivos liderados por Benkos Biohó, San Basilio de Palenque se consolidó como el primer pueblo libre de América tras un acuerdo con la Corona española en 1691. Su aislamiento geográfico en los Montes de María permitió la preservación de tradiciones africanas, particularmente del grupo étnico bantú del Congo-Angola, que se mezclaron con elementos indígenas y coloniales.</p>
                            <p>Este enclave se organizó bajo el sistema ma-kuagro, redes de apoyo comunitario basadas en grupos etarios que aún hoy regulan la solidaridad y el trabajo colectivo.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h3>Lengua Palenquera: Un Código de Resistencia</h3>
                            <p>El palenquero, única lengua criolla de base léxica española y gramática bantú en América, funciona como marcador identitario. Su uso en cantos rituales y narrativas orales ha sido fundamental para transmitir conocimientos ancestrales y mantener la cohesión social frente a la discriminación.</p>
                            <p>Esta lengua no solo codifica su visión del mundo, sino que también estructura prácticas médicas y rituales, evidenciando cómo el lenguaje articula su resistencia cultural.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Línea de tiempo histórica -->
                <h3 data-aos="fade-right" class="mt-5">Cronología Histórica</h3>
                <div class="timeline" data-aos="fade-up">
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1599</h4>
                            <p>Benkos Biohó lidera una rebelión de esclavizados y escapa hacia los Montes de María.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1603-1621</h4>
                            <p>Período de establecimiento y consolidación del palenque como territorio autónomo.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>1691</h4>
                            <p>Acuerdo con la Corona española que reconoce la libertad del palenque.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>Siglos XVIII-XIX</h4>
                            <p>Desarrollo de prácticas culturales distintivas y consolidación de la lengua palenquera.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h4>2005</h4>
                            <p>La UNESCO declara el espacio cultural de San Basilio de Palenque como Patrimonio Inmaterial de la Humanidad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Expresiones Musicales -->
        <section class="tradition-section" id="musica" aria-labelledby="musica-title">
            <div class="container">
                <h2 id="musica-title" data-aos="fade-right">Expresiones Musicales</h2>
                <p data-aos="fade-up">Del Tambor Ancestral a las Fusiones Contemporáneas</p>
                
                <!-- Tabs para organizar la información musical -->
                <div class="tradition-tabs" data-aos="fade-up">
                    <ul class="nav nav-tabs" id="musicTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="generos-tab" data-bs-toggle="tab" data-bs-target="#generos" 
                                    type="button" role="tab" aria-controls="generos" aria-selected="true">
                                Géneros Tradicionales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="instrumentos-tab" data-bs-toggle="tab" data-bs-target="#instrumentos" 
                                    type="button" role="tab" aria-controls="instrumentos" aria-selected="false">
                                Instrumentación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="innovaciones-tab" data-bs-toggle="tab" data-bs-target="#innovaciones" 
                                    type="button" role="tab" aria-controls="innovaciones" aria-selected="false">
                                Innovaciones
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="musicTabsContent">
                        <div class="tab-pane fade show active" id="generos" role="tabpanel" aria-labelledby="generos-tab">
                            <h4>Géneros Tradicionales y su Simbología</h4>
                            <p>La música palenquera se estructura alrededor del tambor, instrumento que simboliza la libertad conquistada. Géneros como el bullerengue sentao, el son de negros y el lumbalú emplean polirritmias complejas que evocan patrones africanos.</p>
                            <p>El son palenquero, surgido en los años 30 al mezclar son cubano con tradiciones locales, se caracteriza por letras en palenquero y el uso del llamador, tambor que marca el ritmo base. Estas expresiones se vinculan a ciclos vitales: el bullerengue acompaña nacimientos y bodas, mientras el lumbalú guía los ritos mortuorios.</p>
                            
                            <figure class="quote-block">
                                <blockquote>
                                    "El tambor no es solo un instrumento, es la voz de nuestros ancestros que sigue hablando a través de nuestras manos."
                                </blockquote>
                                <figcaption>— Rafael Cassiani, músico palenquero</figcaption>
                            </figure>
                        </div>
                        <div class="tab-pane fade" id="instrumentos" role="tabpanel" aria-labelledby="instrumentos-tab">
                            <h4>Instrumentación y Estructura Comunitaria</h4>
                            <p>La fabricación de tambores como el pechiche (tambor hembra) y el cununo (tambor macho) sigue técnicas ancestrales usando troncos de árboles como el caracolí.</p>
                            <p>Los músicos, organizados en agrupaciones como el Sexteto Tabalá o Batata y su Rumba Palenquera, heredan roles específicos: los cantadoras (mujeres vocalistas) lideran las letras, mientras los tamboreros mantienen diálogos rítmicos que imitan lenguajes tonales africanos.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-drum fa-3x mb-3" style="color: var(--color-primary);"></i>
                                        <h5>Tambor Pechiche</h5>
                                        <p>Tambor principal, de tono grave, que marca el ritmo base.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-drum fa-3x mb-3" style="color: var(--color-primary);"></i>
                                        <h5>Tambor Llamador</h5>
                                        <p>Tambor pequeño que "llama" o marca el tiempo.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-microphone fa-3x mb-3" style="color: var(--color-primary);"></i>
                                        <h5>Cantadoras</h5>
                                        <p>Mujeres que preservan los cantos tradicionales.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="innovaciones" role="tabpanel" aria-labelledby="innovaciones-tab">
                            <h4>Innovaciones y Diálogos con Géneros Globales</h4>
                            <p>Las nuevas generaciones han creado fusiones como el electropalenquero de Kombilesa Mí, que integra sintetizadores con tambores tradicionales. Franklyn Tejedor "Lamparita", pionero del colectivo Mitú, combina samples de cantos ancestrales con beats electrónicos, demostrando cómo la tradición se reinterpreta sin perder su esencia.</p>
                            <p>Esta evolución refleja una estrategia de preservación activa, donde el hip hop y la electrónica se convierten en vehículos para narrar luchas contemporáneas contra el racismo y la marginación.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h5>Kombilesa Mí</h5>
                                        <p>Colectivo que fusiona rap en lengua palenquera con ritmos tradicionales, creando un puente entre generaciones.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <h5>Mitú</h5>
                                        <p>Proyecto de música electrónica que incorpora samples de cantos ancestrales palenqueros en producciones contemporáneas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Galería de imágenes musicales -->
                <div class="tradition-gallery" data-aos="fade-up">
                    <h3 class="visually-hidden">Galería de imágenes musicales</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1511192336575-5a79af67a629?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Tambores tradicionales palenqueros" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Tambores tradicionales palenqueros</figcaption>
                            </figure>
                        </div>
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Cantadoras preservando tradiciones orales" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Cantadoras preservando tradiciones orales</figcaption>
                            </figure>
                        </div>
                        <div class="col-md-4">
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                     alt="Fusión de tradición y modernidad" loading="lazy" width="400" height="250">
                                <figcaption class="gallery-caption">Fusión de tradición y modernidad</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- El Lumbalú -->
        <section class="tradition-section" id="lumbalu" aria-labelledby="lumbalu-title">
            <div class="container">
                <h2 id="lumbalu-title" data-aos="fade-right">El Lumbalú: Ritual Fúnebre y Memoria Colectiva</h2>
                
                <div class="row">
                    <div class="col-lg-6" data-aos="fade-up">
                        <h3>Estructura y Simbolismo del Rito</h3>
                        <p>El Lumbalú, practicado durante nueve noches tras un fallecimiento, sintetiza creencias africanas y católicas. Su nombre deriva del kikongo lumbalu (llanto colectivo), y estructura un proceso de duelo que garantiza el tránsito del alma al mundo ancestral.</p>
                        <p>Los kuagros organizan veladas con cantos responsoriales (chandé) y juegos tradicionales como la puya, donde los participantes deben mantener vigilia mediante competencias físicas y mentales.</p>
                    </div>
                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h4>Componentes Rituales</h4>
                            <ul>
                                <li><strong>Cantos de Lumbalú:</strong> Dirigidos por una cantadora, relatan la vida del difunto y guían su espíritu. Las letras en palenquero mezclan invocaciones a santos católicos y deidades africanas como Sángó.</li>
                                <li><strong>Ofrendas Alimenticias:</strong> El sancocho de gallina se prepara con hierbas como el yalua para purificar a los dolientes.</li>
                                <li><strong>Danças Circulares:</strong> Los participantes forman círculos concéntricos que simbolizan la continuidad entre vida y muerte, moviéndose en dirección contraria a las manecillas del reloj para "deshacer" el tiempo terrenal.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-lg-12" data-aos="fade-up">
                        <h3>Significado Antropológico</h3>
                        <p>El Lumbalú opera como mecanismo de cohesión social: al compartir el dolor, la comunidad reafirma su identidad. Comparado con el candomblé brasileño y la santería cubana, este ritual destaca por su énfasis en la participación colectiva sobre el trance individual, reflejando valores comunitarios bantúes.</p>
                        <p>Además, funciona como archivo oral: los cantos preservan historias familiares y episodios de resistencia cimarrona que no aparecen en registros escritos.</p>
                        
                        <figure class="quote-block">
                            <blockquote>
                                "En el Lumbalú no solo despedimos a un ser querido, sino que reafirmamos quiénes somos como pueblo. Cada canto es un hilo que nos conecta con nuestros ancestros."
                            </blockquote>
                            <figcaption>— Graciela Salgado, cantadora de Palenque</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <!-- Medicina Tradicional -->
        <section class="tradition-section" id="medicina" aria-labelledby="medicina-title">
            <div class="container">
                <h2 id="medicina-title" data-aos="fade-right">Medicina Tradicional: Saberes Herbarios y Clasificaciones Térmicas</h2>
                
                <div class="row">
                    <div class="col-lg-12" data-aos="fade-up">
                        <h3>Sistema de Salud Ancestral</h3>
                        <p>La medicina palenquera se basa en el equilibrio entre "frío" y "caliente", categorías que regulan tanto las enfermedades como los ciclos naturales. Esta clasificación, de origen africano, asocia dolencias "calientes" (fiebres, inflamaciones) con plantas "frías" como la albahaca negra (Ocimum basilicum), y viceversa.</p>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="info-card">
                            <h4>Plantas "Frías"</h4>
                            <ul>
                                <li><strong>Albahaca Negra:</strong> Para bajar fiebres e inflamaciones.</li>
                                <li><strong>Hierba Limón:</strong> Calma dolores de cabeza y reduce la presión arterial.</li>
                                <li><strong>Toronjil:</strong> Alivia problemas digestivos y calma nervios.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="info-card">
                            <h4>Plantas "Calientes"</h4>
                            <ul>
                                <li><strong>Jengibre:</strong> Estimula la circulación y combate resfriados.</li>
                                <li><strong>Ruda:</strong> Alivia dolores menstruales y problemas respiratorios.</li>
                                <li><strong>Canela:</strong> Mejora la digestión y fortalece el sistema inmunológico.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <h4>Plantas de Protección</h4>
                            <ul>
                                <li><strong>Cabalonga:</strong> Semillas usadas en rituales de protección contra "brujerías".</li>
                                <li><strong>Indio Desnudo:</strong> Corteza antiinflamatoria para mordeduras de serpiente.</li>
                                <li><strong>Guanábana:</strong> Hojas en decocción para infecciones respiratorias.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-lg-8" data-aos="fade-up">
                        <h3>Transmisión del Conocimiento y Retos Actuales</h3>
                        <p>Los nganga (médicos tradicionales) aprenden mediante apprentissage oral, memorizando propiedades de plantas en canciones y refranes. Sin embargo, la migración juvenil y la penetración de la medicina occidental amenazan esta tradición.</p>
                        <p>Iniciativas como el Jardín Botánico Ancestral, donde cultivan especies clave, buscan preservar estos saberes mediante talleres intergeneracionales.</p>
                    </div>
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <figure class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                                 alt="Jardín de plantas medicinales tradicionales" loading="lazy" width="400" height="250">
                            <figcaption class="gallery-caption">Jardín de plantas medicinales tradicionales</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <!-- Conclusiones -->
        <section class="tradition-section" id="conclusiones" aria-labelledby="conclusiones-title">
            <div class="container">
                <h2 id="conclusiones-title" data-aos="fade-right">Conclusiones: Palenque como Modelo de Resiliencia Cultural</h2>
                
                <div class="row">
                    <div class="col-lg-8 mx-auto" data-aos="fade-up">
                        <p>San Basilio de Palenque ejemplifica cómo comunidades afrodiaspóricas han transformado la opresión en creatividad cultural. Su música, rituales y medicina no son reliquias estáticas, sino sistemas dinámicos que dialogan con la modernidad mientras preservan núcleos identitarios.</p>
                        
                        <div class="info-card mt-4">
                            <h4>Aspectos Clave a Destacar</h4>
                            <ul>
                                <li><strong>Interconexión de Elementos:</strong> La lengua, música y medicina forman un tejido indivisible.</li>
                                <li><strong>Agentes de Cambio:</strong> Colectivos juveniles como Kombilesa Mí muestran que la innovación fortalece, no diluye, la tradición.</li>
                                <li><strong>Amenazas y Oportunidades:</strong> El turismo masivo y la apropiación cultural exigen modelos de divulgación ética que prioricen la voz palenquera.</li>
                            </ul>
                        </div>
                        
                        <p class="mt-4">Al publicar sobre Palenque, se debe evitar el exotismo, enfatizando en cambio su papel como faro de resistencia y fuente de soluciones ancestrales a problemas contemporáneos, desde salud comunitaria hasta justicia social.</p>
                        
                        <figure class="quote-block">
                            <blockquote>
                                "Palenque no es un museo viviente, sino una comunidad que ha sabido adaptar sus tradiciones para sobrevivir y prosperar en un mundo cambiante."
                            </blockquote>
                            <figcaption>— Manuel Pérez, historiador</figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>
    </main>

<!-- Botón para volver arriba -->
<button type="button" class="back-to-top" id="backToTop" aria-label="Volver al inicio de la página">
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Pie de página personalizado -->
    <footer class="custom-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3>San Basilio de Palenque</h3>
                    <p>Primer pueblo libre de América y Patrimonio Inmaterial de la Humanidad por la UNESCO.</p>
                </div>
                <div class="col-md-4">
                    <h3>Enlaces Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="tradiciones.php">Tradiciones</a></li>
                        <li><a href="productos.php">Productos</a></li>
                        <li><a href="historias.php">Historias</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3>Contacto</h3>
                    <address>
                        <p><i class="fas fa-envelope me-2" aria-hidden="true"></i> <a href="mailto:info@sanbasiliodepalenque.com">info@sanbasiliodepalenque.com</a></p>
                        <p><i class="fas fa-phone me-2" aria-hidden="true"></i> <a href="tel:+573123456789">+57 312 345 6789</a></p>
                    </address>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="copyright">© <?php echo date('Y'); ?> San Basilio de Palenque. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery primero para mejor compatibilidad -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous"></script>
    <!-- Luego Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <!-- AOS para animaciones -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Script personalizado -->
    <script>
        /**
         * Script principal para la página de tradiciones
         * Maneja animaciones, navegación y comportamiento interactivo
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar AOS (Animate On Scroll) con configuración optimizada
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100,
                delay: 0
            });
            
            // Gestión del botón para volver arriba
            const backToTopButton = document.getElementById('backToTop');
            
            /**
             * Muestra u oculta el botón de volver arriba según la posición de scroll
             */
            function toggleBackToTopButton() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }
            }
            
            // Escuchar el evento de scroll para mostrar/ocultar el botón
            window.addEventListener('scroll', toggleBackToTopButton);
            
            // Acción al hacer clic en el botón de volver arriba
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            /**
             * Implementa navegación suave para enlaces internos
             * Mejora la experiencia de usuario al navegar entre secciones
             */
            function setupSmoothScrolling() {
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const targetId = this.getAttribute('href');
                        if (targetId === '#') return;
                        
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            // Ajuste para el header fijo
                            const headerOffset = 100;
                            const elementPosition = targetElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                            
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            }
            
            // Configurar navegación suave
            setupSmoothScrolling();
            
            /**
             * Gestiona la navegación por tabs
             * Asegura que los tabs funcionen correctamente y mantiene el estado en la URL
             */
            function setupTabs() {
                // Activar el primer tab por defecto
                const firstTabEl = document.querySelector('#musicTabs button:first-child');
                if (firstTabEl) {
                    const firstTab = new bootstrap.Tab(firstTabEl);
                    firstTab.show();
                }
                
                // Cambiar URL al cambiar de tab (sin recargar la página)
                const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
                tabLinks.forEach(tabLink => {
                    tabLink.addEventListener('shown.bs.tab', function(e) {
                        const targetId = e.target.getAttribute('data-bs-target').substring(1);
                        history.replaceState(null, null, `#${targetId}`);
                    });
                });
                
                // Activar tab según hash en URL al cargar la página
                const hash = window.location.hash.substring(1);
                if (hash && document.getElementById(hash)) {
                    const tabToActivate = document.querySelector(`[data-bs-target="#${hash}"]`);
                    if (tabToActivate) {
                        const tab = new bootstrap.Tab(tabToActivate);
                        tab.show();
                    }
                }
            }
            
            // Configurar tabs
            setupTabs();
            
            /**
             * Mejora la accesibilidad para usuarios de teclado
             * Permite navegar por la página usando solo el teclado
             */
            function enhanceKeyboardAccessibility() {
                // Mejorar navegación por teclado para la galería
                const galleryItems = document.querySelectorAll('.gallery-item');
                galleryItems.forEach(item => {
                    item.setAttribute('tabindex', '0');
                    item.addEventListener('keypress', function(e) {
                        // Activar al presionar Enter o Space
                        if (e.key === 'Enter' || e.key === ' ') {
                            this.querySelector('img').click();
                        }
                    });
                });
            }
            
            // Mejorar accesibilidad
            enhanceKeyboardAccessibility();
            
            /**
             * Implementa carga perezosa (lazy loading) para imágenes
             * Mejora el rendimiento de la página cargando imágenes solo cuando son necesarias
             */
            function setupLazyLoading() {
                // Verificar si el navegador soporta IntersectionObserver
                if ('IntersectionObserver' in window) {
                    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
                    
                    const imageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src || img.src;
                                img.classList.add('loaded');
                                imageObserver.unobserve(img);
                            }
                        });
                    });
                    
                    lazyImages.forEach(img => {
                        imageObserver.observe(img);
                    });
                }
            }
            
            // Configurar lazy loading si hay imágenes con data-src
            setupLazyLoading();
        });
    </script>
    
    <!-- Archivo CSS externo para los estilos específicos de tradiciones -->
    <style>
        /* Este bloque se debe mover a un archivo externo: css/tradiciones.css */
        :root {
            --color-primary: #4CAF50;
            --color-secondary: #FF5722;
            --color-accent: #e74c3c;
            --color-light: #ecf0f1;
            --color-dark: #34495e;
            --color-text: #333;
            --font-main: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-main);
            color: var(--color-text);
            background-color: #f9f9f9;
            padding-top: 100px; /* Espacio para el header fijo */
            scroll-behavior: smooth;
        }

        /* Estilos para el banner principal */
        .tradition-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1518019671582-55004f1bc9ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .tradition-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .tradition-banner p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Estilos para las secciones de contenido */
        .tradition-section {
            padding: 60px 0;
            border-bottom: 1px solid #eee;
        }

        .tradition-section:last-child {
            border-bottom: none;
        }

        .tradition-section h2 {
            color: var(--color-primary);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .tradition-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background-color: var(--color-primary);
        }

        .tradition-section h3 {
            color: var(--color-secondary);
            margin: 25px 0 15px;
            font-weight: 600;
        }

        /* Estilos para las tarjetas de información */
        .info-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover, .info-card:focus-within {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .info-card h4 {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 15px;
            border-left: 4px solid var(--color-primary);
            padding-left: 15px;
        }

        /* Estilos para la galería de imágenes */
        .tradition-gallery {
            margin: 40px 0;
        }

        .gallery-item {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img, .gallery-item:focus img {
            transform: scale(1.05);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-caption, .gallery-item:focus .gallery-caption {
            opacity: 1;
        }

        /* Estilos para las citas destacadas */
        .quote-block {
            background-color: var(--color-light);
            border-left: 5px solid var(--color-primary);
            padding: 20px;
            margin: 30px 0;
            position: relative;
        }

        .quote-block blockquote {
            font-style: italic;
            margin: 0;
            padding: 0 0 0 30px;
        }

        .quote-block blockquote::before {
            content: '"';
            font-size: 60px;
            color: rgba(76, 175, 80, 0.2);
            position: absolute;
            top: -15px;
            left: 10px;
        }

        .quote-block figcaption {
            margin-top: 10px;
            text-align: right;
            font-weight: 500;
        }

        /* Estilos para la sección de tabs */
        .tradition-tabs {
            margin: 40px 0;
        }

        .nav-tabs .nav-link {
            color: var(--color-dark);
            border: none;
            padding: 15px 20px;
            font-weight: 500;
            border-radius: 0;
            border-bottom: 3px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--color-primary);
            background: transparent;
            border-bottom: 3px solid var(--color-primary);
        }

        .tab-content {
            padding: 30px;
            background: white;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Estilos para la línea de tiempo */
        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 40px auto;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: var(--color-light);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            background-color: var(--color-primary);
            border-radius: 50%;
            top: 15px;
            z-index: 1;
        }

        .timeline-item:nth-child(odd)::after {
            right: -12px;
        }

        .timeline-item:nth-child(even)::after {
            left: -13px;
        }

        /* Botón para volver arriba */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--color-primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease, background-color 0.3s ease;
            z-index: 1000;
            border: none;
        }

        .back-to-top:hover, .back-to-top:focus {
            background-color: var(--color-secondary);
        }

        .back-to-top.visible {
            opacity: 1;
        }

        /* Estilos para el pie de página personalizado */
        .custom-footer {
            background-color: var(--color-dark);
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }

        .custom-footer h3 {
            color: var(--color-primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover, .footer-links a:focus {
            color: var(--color-primary);
            text-decoration: underline;
        }

        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s ease;
            display: inline-block;
        }

        .social-icons a:hover, .social-icons a:focus {
            color: var(--color-primary);
        }

        .copyright {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Mejoras de accesibilidad */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        /* Estilos para dispositivos móviles */
        @media (max-width: 768px) {
            .tradition-banner h1 {
                font-size: 2rem;
            }
            
            .tradition-banner p {
                font-size: 1rem;
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
            
            .timeline-item::after {
                left: 18px;
            }
            
            .timeline-item:nth-child(odd)::after {
                right: auto;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .tab-content {
                padding: 20px 15px;
            }
            
            .info-card {
                padding: 15px;
            }
        }

        /* Mejoras para pantallas grandes */
        @media (min-width: 1200px) {
            .container {
                max-width: 1140px;
            }
            
            .tradition-banner {
                padding: 150px 0;
            }
            
            .tradition-banner h1 {
                font-size: 3rem;
            }
        }

        /* Animaciones para mejorar la experiencia de usuario */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        /* Mejoras para impresión */
        @media print {
            .back-to-top, 
            .nav-tabs, 
            .custom-footer {
                display: none !important;
            }
            
            body {
                padding-top: 0;
            }
            
            .tradition-section {
                page-break-inside: avoid;
                border-bottom: none;
            }
            
            .info-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .tradition-banner {
                background: #f9f9f9 !important;
                color: #333 !important;
                padding: 20px 0;
            }
            
            .tradition-banner h1 {
                color: var(--color-primary) !important;
            }
        }
    </style>
</body>
</html>