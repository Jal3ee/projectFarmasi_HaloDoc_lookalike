<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="multikit" />
    <meta name="keywords" content="multikit" />
    <title>Multikit - Multi-purpose Html Template</title>
    <link rel="manifest" href="https://themes.pixelstrap.net/multikit/manifest.json" />
    <meta name="theme-color" content="#ff8d2f" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-title" content="multikit" />
    <meta name="msapplication-TileImage" content="https://themes.pixelstrap.net/multikit/assets/images/favicon/1.svg" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="icon" type="image/x-icon" href="https://themes.pixelstrap.net/multikit/assets/images/favicon/9.svg" />
    <link rel="shortcut icon" href="https://themes.pixelstrap.net/" type="image/x-icon" />

    <!--Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />

    <!-- Bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/vendors/bootstrap.css" />

    <!-- Swiper css -->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/vendors/swiper/swiper-bundle.min.css" />

    <!-- Remix Icon css -->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/remixicon.css" />

    <!-- Style css -->
    <link id="change-link" rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/style.css" />
    <!-- Questions css -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/custom.css" />
</head>

<body class="inter-body learning-color">
    <!-- header start -->
    <header class="main-header learning-header h-102">
        <div class="custom-container">
            <div class="row">
                <div class="col-12">
                    <div class="top-header">
                        <div class="header-left">
                            <a class="text-white" href="content-screen.html">
                                <i class="ri-arrow-left-line"></i>
                            </a>
                        </div>


                    </div>

                    <div class="header-bottom header-bottom-2">
                        <h2 class="fw-500 text-white">Cources Data</h2>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header end -->

    <section class="pre-test-section pt-25">
        <div class="custom-container">
            <button class="btn bg-web-primary text-white mb-3" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                <i class="bi bi-plus-circle"></i> Add New Video
            </button>

            <div id="videoList" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

            </div>

            <nav aria-label="Page navigation" class="mt-4 pb-5">
                <ul id="pagination" class="pagination"></ul>
            </nav>
        </div>
    </section>

    <!-- Add Video Modal -->
    <div class="modal fade" id="addVideoModal" tabindex="-1" aria-labelledby="addVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVideoModalLabel">Add New Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addVideoForm">
                        <div class="mb-3">
                            <label for="newVideoLink" class="form-label">YouTube Video Link</label>
                            <input type="text" class="form-control" id="newVideoLink" required>
                        </div>
                        <div class="mb-3">
                            <label for="newVideoTitle" class="form-label">Video Title</label>
                            <input type="text" class="form-control" id="newVideoTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="newVideoDescription" class="form-label">Video Description</label>
                            <textarea class="form-control" id="newVideoDescription" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" onclick="addVideo()">Add Video</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Video Modal -->
    <div class="modal fade" id="editVideoModal" tabindex="-1" aria-labelledby="editVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVideoModalLabel">Edit Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editVideoForm">
                        <input type="hidden" id="editVideoId">
                        <div class="mb-3">
                            <label for="editVideoLink" class="form-label">YouTube Video Link</label>
                            <input type="text" class="form-control" id="editVideoLink" required>
                        </div>
                        <div class="mb-3">
                            <label for="editVideoTitle" class="form-label">Video Title</label>
                            <input type="text" class="form-control" id="editVideoTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editVideoDescription" class="form-label">Video Description</label>
                            <textarea class="form-control" id="editVideoDescription" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" onclick="saveEditedVideo()">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap js-->
    <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <!-- swiper js -->
    <script src="<?= base_url() ?>assets/js/swiper-bundle.min.js"></script>
    <script src="<?= base_url() ?>assets/js/custom_swiper.js"></script>

    <!-- image change js -->
    <script src="<?= base_url() ?>assets/js/image-change.js"></script>

    <!-- Theme js-->
    <script src="<?= base_url() ?>assets/js/script.js"></script>

    <!-- Theme Settings js-->
    <script src="<?= base_url() ?>assets/js/theme-setting.js"></script>

    <!-- Youtube APi -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <script>
        const videoData = <?= !empty($courses) ? json_encode($courses) : '[]' ?>;
    </script>

    <!-- Cources js -->
    <script src="<?= base_url() ?>assets/js/cources.js"></script>
</body>

</html>