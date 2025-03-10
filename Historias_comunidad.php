<?php
// Iniciar sesión para gestionar el estado del usuario
session_start();

// Incluir la conexión a la base de datos
include 'db.php'; // Esto carga $conexion

// Variables para metadatos de la página
$pageTitle = "Historias de San Basilio de Palenque";
$pageDescription = "Descubre las historias, mitos y leyendas que forman parte del patrimonio cultural de San Basilio de Palenque";

// Procesar el envío del comentario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario']) && isset($_SESSION['usuario_id'])) {
    if (!isset($conexion) || mysqli_connect_errno()) {
        echo "<p class='text-danger'>Error: No se pudo conectar a la base de datos.</p>";
    } else {
        $usuario_id = $_SESSION['usuario_id'];
        $comentario = trim($_POST['comentario']);

        if (!empty($comentario)) {
            $stmt = mysqli_prepare($conexion, "INSERT INTO comentarios (usuario_id, comentario) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "is", $usuario_id, $comentario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: historias_comunidad.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords"
        content="San Basilio de Palenque, historias, mitos, leyendas, Benkos Biohó, Lumbalú, Festival de Tambores">

    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
    :root {
        --primary-color: #8B4513;
        /* Marrón tierra */
        --secondary-color: #F5DEB3;
        /* Beige claro */
        --accent-color: #CD853F;
        /* Marrón claro */
        --text-color: #333333;
        --light-bg: #FFF8E7;
        --dark-bg: #3E2723;
    }

    body {
        font-family: 'Lora', serif;
        color: var(--text-color);
        background-color: var(--light-bg);
        line-height: 1.8;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    /* Header y navegación */
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
        background-color: var(--primary-color);
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

    .login-btn,
    .register-btn {
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

    .login-btn i,
    .register-btn i {
        margin-right: 5px;
    }

    /* Estilos para el banner principal */
    .main-banner {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('palenque-banner.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 8rem 0 4rem;
        margin-bottom: 3rem;
        text-align: center;
    }

    .main-banner h1 {
        font-size: 3.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1rem;
    }

    .main-banner p {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    /* Estilos para las tarjetas de historias */
    .story-card {
        background-color: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 3rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .story-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }

    .story-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1.5rem;
        position: relative;
    }

    .story-type {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background-color: var(--accent-color);
        color: white;
        padding: 0.3rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .story-date {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }

    .story-title {
        margin-bottom: 0;
        font-size: 2rem;
    }

    .story-content {
        padding: 2rem;
    }

    .story-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }

    .story-card:hover .story-image {
        transform: scale(1.05);
    }

    .story-section {
        margin-bottom: 2rem;
    }

    .story-section h3 {
        color: var(--primary-color);
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .story-footer {
        background-color: var(--secondary-color);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .media-gallery {
        margin-top: 2rem;
    }

    .media-title {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
        border-bottom: 2px solid var(--accent-color);
        padding-bottom: 0.5rem;
        display: inline-block;
    }

    .gallery-item {
        margin-bottom: 1rem;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover img {
        transform: scale(1.05);
    }

    .gallery-caption {
        background-color: var(--secondary-color);
        padding: 0.8rem;
        font-size: 0.9rem;
    }

    /* Estilos para el footer */
    footer {
        background-color: var(--dark-bg);
        color: white;
        padding: 3rem 0;
        margin-top: 4rem;
    }

    .footer-title {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }

    .footer-links a {
        color: white;
        text-decoration: none;
        display: block;
        margin-bottom: 0.8rem;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: var(--secondary-color);
    }

    .footer-social a {
        color: white;
        font-size: 1.5rem;
        margin-right: 1rem;
        transition: color 0.3s ease;
    }

    .footer-social a:hover {
        color: var(--secondary-color);
    }

    .footer-bottom {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 1rem 0;
        margin-top: 2rem;
        text-align: center;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .main-banner h1 {
            font-size: 2.5rem;
        }

        .auth-section {
            margin-top: 15px;
            justify-content: center;
            width: 100%;
        }

        .user-welcome,
        .auth-buttons {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 767.98px) {
        .story-image {
            height: 300px;
        }

        .gallery-item img {
            height: 150px;
        }
    }

    /* Estilos para el formulario de comentarios */
    .comment-form textarea {
        border: 1px solid #ced4da;
        border-radius: 10px;
        resize: vertical;
        transition: border-color 0.3s ease;
    }

    .comment-form textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(139, 69, 19, 0.25);
    }

    .comment-form button {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        transition: all 0.3s ease;
    }

    .comment-form button:hover {
        background-color: #6b3510;
        border-color: #6b3510;
        transform: translateY(-2px);
    }

    .comment-card {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        border-left: 4px solid var(--primary-color);
    }

    .animate__animated {
        animation-duration: 1s;
    }

    .animate__fadeInUp {
        animation-name: fadeInUp;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 50px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    </style>
</head>

<body>
    <!-- Header con navegación -->
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
                            <a class="nav-link" href="tradiciones.php">Tradiciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="productos.php">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="historias_comunidad.php" aria-current="page">Historias</a>
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

    <!-- Banner principal -->
    <section class="main-banner">
        <div class="container">
            <h1>Historias de San Basilio de Palenque</h1>
            <p>Descubre las leyendas, mitos y eventos históricos que han forjado la identidad cultural del primer pueblo
                libre de América</p>
        </div>
    </section>

    <!-- Contenido principal -->
    <main class="container">
        <!-- Introducción -->
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto">
                <p class="lead text-center">San Basilio de Palenque es un territorio de memoria viva donde cada historia
                    es un acto de resistencia cultural. Estas narrativas no son simples relatos del pasado, sino
                    herramientas de preservación identitaria que conectan a las nuevas generaciones con sus raíces
                    africanas y cimarronas.</p>
            </div>
        </div>

        <!-- Primera historia: Benkos Biohó -->
        <article class="story-card shadow-lg rounded-3" id="benkos-bioho">
            <header class="story-header">
                <span class="story-type">Historia Fundacional</span>
                <div class="story-date">6 de marzo de 1621</div>
                <h2 class="story-title">La Ejecución de Benkos Biohó</h2>
            </header>

            <div class="story-content">
                <img src="https://s3.amazonaws.com/rtvc-assets-senalmemoria.gov.co/s3fs-public/styles/1200_x_675_escalado/public/field_image/WEB-Benkos-Bioh%C3%B3-y-el-Palenque-de-San-Basilio.jpg?itok=JJpcul9D"
                    alt="Estatua de Benkos Biohó en San Basilio de Palenque" class="story-image">

                <section class="story-section">
                    <h3>Contexto Histórico</h3>
                    <p>Benkos Biohó, líder guerrero procedente del reino de Bissau (actual Guinea-Bisáu), llegó
                        esclavizado a Cartagena en 1599. Tras fugarse en 1603, lideró una rebelión cimarrona que culminó
                        en la creación de múltiples palenques en los Montes de María. Su captura y ejecución en 1621
                        marcaron un hito en la lucha por la libertad en América.</p>

                    <p>Durante casi dos décadas, Benkos organizó un sistema de resistencia que desafió al poder colonial
                        español. Estableció rutas de escape para personas esclavizadas y negoció con las autoridades
                        coloniales, logrando un reconocimiento temporal de autonomía para su comunidad. Sin embargo, las
                        tensiones con el gobierno colonial culminaron con su captura y posterior ejecución pública en
                        Cartagena.</p>
                </section>

                <section class="story-section">
                    <h3>El Mito del Guerrero Inmortal</h3>
                    <p>La tradición oral palenquera narra que Benkos, tras ser ahorcado en la Plaza de la Media Luna
                        (Cartagena), regresó convertido en ave para guiar a su pueblo. Este relato explica por qué su
                        estatua en la plaza central de San Basilio lo muestra rompiendo cadenas con los brazos alzados,
                        simbolizando su transfiguración en espíritu protector.</p>

                    <p>Según los mayores de la comunidad, el espíritu de Benkos se manifiesta en forma de gavilán
                        durante momentos críticos para el pueblo palenquero. Esta creencia ha sido fundamental para
                        mantener viva la memoria de resistencia y ha inspirado movimientos contemporáneos de
                        reivindicación cultural y territorial.</p>
                </section>

                <div class="media-gallery">
                    <h4 class="media-title">Registros Visuales</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://cloudfront-us-east-1.images.arcpublishing.com/semana/XO4LIOPMJZENPBN2EFBME477JU.jpg"
                                    alt="Estatua de Benkos Biohó en la plaza principal de San Basilio de Palenque">
                                <div class="gallery-caption">Estatua de Benkos Biohó ubicada en la plaza principal, su
                                    imagen aparece en documentales como "Palenque" (2018) del Ministerio de Cultura.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://assets.grok.com/users/2c8afa46-66c9-4bb2-a7a2-bf6f79368149/nQxlWtrNnca3FBMa-generated_image.jpg"
                                    alt="Ilustración colonial de la captura de Benkos Biohó">
                                <div class="gallery-caption">El Archivo General de la Nación conserva ilustraciones
                                    coloniales de su captura (Códice de la Inquisición, 1621).</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="story-footer">
                <div class="story-tags">
                    <span class="badge bg-secondary me-1">Historia</span>
                    <span class="badge bg-secondary me-1">Resistencia</span>
                    <span class="badge bg-secondary me-1">Cimarronaje</span>
                </div>
                <div class="story-share">
                    <span class="me-2">Compartir:</span>
                    <a href="#" class="me-2" aria-label="Compartir en Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2" aria-label="Compartir en Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Compartir en WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </footer>
        </article>

        <!-- Segunda historia: Catalina Loango y el Lumbalú -->
        <article class="story-card shadow-lg rounded-3" id="catalina-loango">
            <header class="story-header">
                <span class="story-type">Mito Ancestral</span>
                <div class="story-date">Siglo XVII</div>
                <h2 class="story-title">Catalina Loango de Angola y el Ritual del Lumbalú</h2>
            </header>

            <div class="story-content">
                <img src="https://assets.grok.com/users/2c8afa46-66c9-4bb2-a7a2-bf6f79368149/mHMydyU0aTswKVao-generated_image.jpg"
                    alt="Ritual del Lumbalú en San Basilio de Palenque" class="story-image">

                <section class="story-section">
                    <h3>La Leyenda</h3>
                    <p>Catalina, mujer angoleña esclavizada en el siglo XVII, desapareció tras caer al arroyo Cano Dulce
                        mientras pescaba. Según la tradición, emergió días después convertida en mediadora entre vivos y
                        muertos, estableciendo las bases del Lumbalú, ritual fúnebre de nueve noches.</p>

                    <p>Los ancianos relatan que Catalina regresó con conocimientos espirituales adquiridos durante su
                        estancia en el mundo acuático, donde los espíritus de los ancestros le enseñaron cómo guiar a
                        las almas en su tránsito. Este conocimiento se convirtió en la base del ritual funerario más
                        importante de la comunidad palenquera.</p>
                </section>

                <section class="story-section">
                    <h3>Simbología y Práctica Actual</h3>
                    <p>El Lumbalú integra cantos en palenquero (chandé), danzas circulares y ofrendas de sancocho de
                        gallina. Los participantes usan atuendos blancos para "purificar el camino del alma", mientras
                        barrer la casa hacia afuera simboliza alejar la muerte.</p>

                    <p>Durante las nueve noches del ritual, se realizan diferentes actividades simbólicas:</p>

                    <ul>
                        <li><strong>Primera noche:</strong> Invocación a Catalina y preparación del altar con
                            fotografías del difunto.</li>
                        <li><strong>Noches intermedias:</strong> Cantos en lengua palenquera que narran la vida del
                            fallecido y su linaje.</li>
                        <li><strong>Novena noche:</strong> Despedida final con tambores pechiche que "rompen" el vínculo
                            del alma con el mundo terrenal.</li>
                    </ul>

                    <p>Este ritual no solo cumple una función espiritual, sino que refuerza los lazos comunitarios y
                        preserva la memoria colectiva a través de la oralidad.</p>
                </section>

                <div class="media-gallery">
                    <h4 class="media-title">Registros Audiovisuales</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://assets.grok.com/users/2c8afa46-66c9-4bb2-a7a2-bf6f79368149/hZY1JNkK7wnmqbH4-generated_image.jpg"
                                    alt="Escena del documental 'Lumbalú: El Llanto que Libera'">
                                <div class="gallery-caption">Documental "Lumbalú: El Llanto que Libera" (2022, Señal
                                    Colombia): Muestra una ceremonia completa filmada en 2021.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://assets.grok.com/users/2c8afa46-66c9-4bb2-a7a2-bf6f79368149/9lFoZQlMjJnHqRfC-generated_image.jpg"
                                    alt="Altar con velas y retrato de Catalina Loango">
                                <div class="gallery-caption">La colección Memorias del Palenque (Biblioteca Luis Ángel
                                    Arango) incluye imágenes de altares con velas y retratos de Catalina dibujados por
                                    ancianas cantadoras.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/cGiG1OLwi6I"
                            title="Fragmento del documental sobre el Lumbalú" allowfullscreen></iframe>
                    </div>
                    <p class="text-center mt-2 text-muted">Fragmento del documental "Lumbalú"</p>
                </div>
            </div>

            <footer class="story-footer">
                <div class="story-tags">
                    <span class="badge bg-secondary me-1">Ritual</span>
                    <span class="badge bg-secondary me-1">Funeral</span>
                    <span class="badge bg-secondary me-1">Tradición Oral</span>
                </div>
                <div class="story-share">
                    <span class="me-2">Compartir:</span>
                    <a href="#" class="me-2" aria-label="Compartir en Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2" aria-label="Compartir en Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Compartir en WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </footer>
        </article>

        <!-- Tercera historia: Festival de Tambores -->
        <article class="story-card shadow-lg rounded-3" id="festival-tambores">
            <header class="story-header">
                <span class="story-type">Evento Patrimonial</span>
                <div class="story-date">Octubre Anual (desde 1985)</div>
                <h2 class="story-title">Festival de Tambores y Expresiones Culturales</h2>
            </header>

            <div class="story-content">
                <img src="https://discovercartagena.com.co/wp-content/uploads/2022/04/IMG_6605-1024x768.jpeg"
                    alt="Festival de Tambores de San Basilio de Palenque" class="story-image">

                <section class="story-section">
                    <h3>Historia Viva</h3>
                    <p>Iniciado en 1985 para conmemorar los 350 años del tratado de libertad, este festival reúne
                        expresiones musicales como:</p>

                    <ul>
                        <li><strong>Son palenquero:</strong> Fusión de décimas españolas con tambores pechiche y
                            llamador.</li>
                        <li><strong>Electropalenquero:</strong> Género moderno que mezcla beats electrónicos con cantos
                            ancestrales, popularizado por grupos como Kombilesa Mí.</li>
                    </ul>

                    <p>El festival surgió como una iniciativa comunitaria para preservar y difundir las expresiones
                        culturales palenqueras en un momento en que muchas tradiciones estaban en riesgo de desaparecer
                        debido a la migración y la influencia de medios masivos. Con el tiempo, se ha convertido en uno
                        de los eventos culturales más importantes de la región Caribe colombiana.</p>
                </section>

                <section class="story-section">
                    <h3>Edición 2024: Mujeres y Memoria</h3>
                    <p>Del 11 al 14 de octubre de 2024, el evento destacó el rol de las matronas en la transmisión
                        cultural. Actividades incluyeron:</p>

                    <ul>
                        <li>Talleres de peinados brasilete (trenzas con significados comunitarios).</li>
                        <li>Exhibición de plantas medicinales usadas por ngangas (médicas tradicionales).</li>
                    </ul>

                    <p>La edición de 2024 puso especial énfasis en el papel de las mujeres como guardianas de la memoria
                        colectiva. Las matronas palenqueras, muchas de ellas con más de 80 años, compartieron sus
                        conocimientos sobre medicina tradicional, gastronomía ancestral y técnicas de peinado que
                        contienen códigos culturales transmitidos desde la época de la esclavitud.</p>

                    <p>El festival también incluyó conversatorios sobre el papel de las mujeres en la resistencia
                        histórica y contemporánea, destacando figuras como Graciela Salgado, reconocida cantadora que ha
                        llevado la música palenquera a escenarios internacionales.</p>
                </section>

                <div class="media-gallery">
                    <h4 class="media-title">Registros Multimedia</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://radionacional-v3.s3.amazonaws.com/s3fs-public/node/article/field_image/WhatsApp%20Image%202023-10-17%20at%206.43.00%20AM.jpeg"
                                    alt="Transmisión en vivo del Festival de Tambores">
                                <div class="gallery-caption">Transmisión en vivo: Disponible en Radionacional.co con
                                    secuencias de bailes mapalé y entrevistas a artesanos.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="gallery-item">
                                <img src="https://colombiavisible.com/wp-content/uploads/2023/05/Mujeres-mariimberas-1-1024x576.jpg"
                                    alt="Mujeres tocando marímbula durante el festival">
                                <div class="gallery-caption">Fotos oficiales: La página del festival
                                    (festivaldetambores.com) muestra a mujeres tocando marímbula (instrumento de
                                    laméllas africano).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="story-section mt-4">
                    <h3>Impacto Cultural y Turístico</h3>
                    <p>El Festival de Tambores ha transformado la economía local, generando oportunidades para
                        artesanos, cocineras tradicionales y guías culturales. Cada año, más de 5,000 visitantes
                        nacionales e internacionales llegan a San Basilio durante los días del evento, contribuyendo a
                        la sostenibilidad económica de la comunidad.</p>

                    <p>Además de su impacto económico, el festival ha sido fundamental para el reconocimiento de San
                        Basilio de Palenque como Patrimonio Cultural Inmaterial de la Humanidad por la UNESCO en 2005.
                        Esta designación ha fortalecido los esfuerzos de preservación cultural y ha dado mayor
                        visibilidad a las tradiciones palenqueras a nivel global.</p>
                </section>

                <div class="mt-4">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/Q4FxwlvZiPg" title="Festival de Tambores 2023"
                            allowfullscreen></iframe>
                    </div>
                    <p class="text-center mt-2 text-muted">Festival de Tambores y Expresiones Culturales</p>
                </div>
            </div>

            <footer class="story-footer">
                <div class="story-tags">
                    <span class="badge bg-secondary me-1">Festival</span>
                    <span class="badge bg-secondary me-1">Música</span>
                    <span class="badge bg-secondary me-1">Patrimonio</span>
                </div>
                <div class="story-share">
                    <span class="me-2">Compartir:</span>
                    <a href="#" class="me-2" aria-label="Compartir en Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2" aria-label="Compartir en Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Compartir en WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </footer>
        </article>

        <!-- Análisis comparativo -->
        <section class="analysis-section my-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Análisis Comparativo de las Historias</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Historia</th>
                                    <th>Tipo</th>
                                    <th>Fecha Clave</th>
                                    <th>Elementos Culturales</th>
                                    <th>Soporte Documental</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Benkos Biohó</td>
                                    <td>Historia/Mito</td>
                                    <td>6 de marzo 1621</td>
                                    <td>Estatua, narrativas orales</td>
                                    <td>Ilustraciones coloniales, documentales</td>
                                </tr>
                                <tr>
                                    <td>Catalina Loango</td>
                                    <td>Mito/Práctica ritual</td>
                                    <td>Siglo XVII</td>
                                    <td>Lumbalú, arroyo Cano Dulce</td>
                                    <td>Documentales etnográficos, registros sonoros</td>
                                </tr>
                                <tr>
                                    <td>Festival Tambores</td>
                                    <td>Evento comunitario</td>
                                    <td>Octubre anual</td>
                                    <td>Tambores, peinados, medicina</td>
                                    <td>Transmisiones en vivo, fotografías</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <p>Estas historias, respaldadas por registros tangibles, evidencian cómo San Basilio transforma
                            su pasado en herramientas de resistencia identitaria. La estatua de Benkos no es solo un
                            monumento, sino un símbolo vivo que inspira a jóvenes activistas; el Lumbalú, más que un
                            ritual, es un acto político de memoria, y el Festival de Tambores funciona como diplomacia
                            cultural que proyecta su legado globalmente.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de comentarios -->
        <section class="comments-section my-5">
            <h3 class="mb-4">Comparte tu experiencia</h3>

            <?php if(isset($_SESSION['usuario'])): ?>
            <form class="comment-form mb-4" method="POST" action="historias_comunidad.php">
                <div class="mb-3">
                    <label for="commentText" class="form-label">Tu comentario</label>
                    <textarea class="form-control" id="commentText" name="comentario" rows="3"
                        placeholder="Comparte tu experiencia o conocimiento sobre estas historias..."
                        required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Publicar comentario</button>
            </form>
            <?php else: ?>
            <div class="alert alert-info">
                <p class="mb-0">Para dejar un comentario, por favor <a href="login.php">inicia sesión</a> o <a
                        href="register.php">regístrate</a>.</p>
            </div>
            <?php endif; ?>

            <div class="existing-comments">
                <h4 class="mb-3">Comentarios recientes</h4>
                <?php
        if (!isset($conexion) || mysqli_connect_errno()) {
            echo "<p class='text-danger'>Error: No se pudo conectar a la base de datos para mostrar los comentarios.</p>";
        } else {
            $sql = "SELECT c.comentario, c.fecha_publicacion, u.nombre 
                    FROM comentarios c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    ORDER BY c.fecha_publicacion DESC 
                    LIMIT 5";
            $result = mysqli_query($conexion, $sql);

            if ($result === false) {
                echo "<p class='text-danger'>Error en la consulta: " . mysqli_error($conexion) . "</p>";
            } elseif (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $comment_date = date('d/m/Y H:i', strtotime($row['fecha_publicacion']));
                    echo '<div class="comment-card mb-3">';
                    echo '<div class="comment-header d-flex align-items-center mb-2">';
                    echo '<img src="https://via.placeholder.com/40" alt="Avatar de ' . htmlspecialchars($row['nombre']) . '" class="rounded-circle me-2" width="40" height="40">';
                    echo '<div>';
                    echo '<h5 class="mb-0">' . htmlspecialchars($row['nombre']) . '</h5>';
                    echo '<small class="text-muted">' . $comment_date . '</small>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="comment-body">';
                    echo '<p>' . htmlspecialchars($row['comentario']) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-muted">No hay comentarios aún. ¡Sé el primero en compartir tu experiencia!</p>';
            }
            mysqli_close($conexion);
        }
        ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">San Basilio de Palenque</h4>
                    <p>Primer pueblo libre de América y Patrimonio Cultural Inmaterial de la Humanidad por la UNESCO
                        desde 2005.</p>
                    <div class="footer-social mt-3">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">Enlaces rápidos</h4>
                    <div class="footer-links">
                        <a href="index.php">Inicio</a>
                        <a href="tradiciones.php">Tradiciones</a>
                        <a href="productos.php">Productos</a>
                        <a href="historias_comunidad.php">Historias</a>
                        <a href="contacto.php">Contacto</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <h4 class="footer-title">Contacto</h4>
                    <address>
                        <p><i class="fas fa-map-marker-alt me-2"></i> San Basilio de Palenque, Bolívar, Colombia</p>
                        <p><i class="fas fa-phone me-2"></i> +57 (5) 123-4567</p>
                        <p><i class="fas fa-envelope me-2"></i> info@sanbasiliodepalenque.com</p>
                    </address>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p class="mb-0">© <?php echo date('Y'); ?> San Basilio de Palenque. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Botón para volver arriba -->
    <button id="back-to-top" class="btn btn-primary rounded-circle" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Modal para imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Imagen ampliada">
                </div>
                <div class="modal-footer">
                    <p id="modalCaption" class="w-100 text-center mb-0"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header');
        const body = document.body;
        body.style.paddingTop = header.offsetHeight + 'px';

        const backToTopButton = document.getElementById('back-to-top');
        if (backToTopButton) {
            backToTopButton.style.position = 'fixed';
            backToTopButton.style.bottom = '20px';
            backToTopButton.style.right = '20px';
            backToTopButton.style.display = 'none';
            backToTopButton.style.zIndex = '99';

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });

            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        const galleryItems = document.querySelectorAll('.gallery-item img');
        const modalImage = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalCaption');
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));

        galleryItems.forEach(item => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', function() {
                modalImage.src = this.src;
                modalImage.alt = this.alt;
                const caption = this.closest('.gallery-item').querySelector('.gallery-caption');
                modalCaption.textContent = caption ? caption.textContent : this.alt;
                imageModal.show();
            });
        });

        const storyCards = document.querySelectorAll('.story-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        storyCards.forEach(card => observer.observe(card));
    });
    </script>
</body>

</html>