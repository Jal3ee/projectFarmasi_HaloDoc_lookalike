<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="multikit" />
    <meta name="keywords" content="multikit" />
    <title>Bakul Sehat ULM</title>
    <link rel="icon" href="<?= base_url() ?>assets/images/favicon/logo.jpg" />
    <link rel="manifest" href="https://themes.pixelstrap.net/multikit/manifest.json" />
    <meta name="theme-color" content="#ff8d2f" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-title" content="multikit" />
    <meta name="msapplication-TileImage" content="https://themes.pixelstrap.net/multikit/assets/images/favicon/1.svg" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/vendors/bootstrap.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/vendors/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/remixicon.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
</head>

<body class="inter-body learning-color">
    <!-- Header -->
    <header class="main-header learning-header h-102">
        <div class="custom-container">
            <div class="row">
                <div class="col-12">
                    <div class="top-header">
                        <div class="header-left">
                            <a class="text-white" href="javascript:history.back()">
                                <i class="ri-arrow-left-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="header-bottom header-bottom-2">
                        <h2 class="fw-500 text-white">Hasil Post-Test Pasien</h2>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <section id="primary_goal">
        <div class="container mt-3">
            <h2 class="mb-4 fw-bold">Daftar Nilai Post-test</h2>

            <?php if (!empty($users) && is_array($users)): ?>
                <div class="table-responsive">
                    <table id="posttestTable" class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Benar / Total</th>
                                <th>Nilai (%)</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <?php
                                    $benar = $u['jumlah_benar'] ?? 0;
                                    $total = $u['jumlah_soal'] ?? 0;
                                    $skor = $total > 0 ? round(($benar / $total) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td><?= esc($u['id']) ?></td>
                                    <td><?= esc($u['nama']) ?></td>
                                    <td><?= $benar ?> / <?= $total ?></td>
                                    <td><strong><?= $skor ?>%</strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?= $u['id_pasien'] ?>">Detail</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Modal Detail Jawaban -->
                    <?php foreach ($users as $u): ?>
                        <div class="modal fade" id="detailModal<?= $u['id_pasien'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $u['id_pasien'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel<?= $u['id_pasien'] ?>">Detail Jawaban - <?= esc($u['nama']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (!empty($u['jawaban'])): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>No.</th>
                                                            <th>Pertanyaan</th>
                                                            <th>Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($u['jawaban'] as $index => $j): ?>
                                                            <tr>
                                                                <td><?= $index + 1 ?></td>
                                                                <td><?= esc($j['soal']['pertanyaan']) ?></td>
                                                                <td>
                                                                    <?php if ($j['benar']): ?>
                                                                        <span class="badge bg-success"><i class="ri-check-line"></i> Benar</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger"><i class="ri-close-line"></i> Salah</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">Tidak ada data jawaban.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">Tidak ada data post-test yang tersedia.</div>
            <?php endif; ?>
        </div>
    </section>
<!-- jQuery CDN - HARUS SEBELUM DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables CSS dan JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#posttestTable').DataTable({
            "language": {
                "search": "Cari Nama:",
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(disaring dari total _MAX_ entri)"
            }
        });
    });
</script>


    <!-- Footer Scripts -->
    <!-- <script src="<?= base_url() ?>assets/js/jquery.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="<?= base_url() ?>assets/js/swiper-bundle.min.js"></script>
    <script src="<?= base_url() ?>assets/js/custom_swiper.js"></script>
    <script src="<?= base_url() ?>assets/js/image-change.js"></script>
    <script src="<?= base_url() ?>assets/js/script.js"></script>
    <script src="<?= base_url() ?>assets/js/theme-setting.js"></script>
</body>

</html>
