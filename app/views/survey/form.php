<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?></title>
    <!-- AdminLTE / Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .card-primary.card-outline {
            border-top: 3px solid #007bff;
        }

        /* Emoji Rating Styles */
        .rating-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .rating-item {
            flex: 1;
            min-width: 80px;
            /* Forces 2x2 on small text */
        }

        .rating-input {
            display: none;
        }

        .rating-label {
            display: block;
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .rating-icon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 8px;
            transition: transform 0.2s;
            filter: grayscale(100%);
            opacity: 0.6;
        }

        .rating-text {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
            color: #6c757d;
        }

        /* Hover State */
        .rating-label:hover {
            border-color: #adb5bd;
            transform: translateY(-2px);
        }

        .rating-label:hover .rating-icon {
            filter: grayscale(0%);
            opacity: 1;
        }

        /* Checked States */
        .rating-input:checked+.rating-label {
            border-width: 2px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .rating-input:checked+.rating-label .rating-text {
            color: #333;
        }

        .rating-input:checked+.rating-label .rating-icon {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.2);
        }

        /* Color Coding based on Value */
        /* 1: Tidak Baik (Red) */
        .rating-item:nth-child(1) .rating-input:checked+.rating-label {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        /* 2: Kurang Baik (Orange) */
        .rating-item:nth-child(2) .rating-input:checked+.rating-label {
            border-color: #ffc107;
            background-color: #fff9e6;
        }

        /* 3: Baik (Green) */
        .rating-item:nth-child(3) .rating-input:checked+.rating-label {
            border-color: #28a745;
            background-color: #e8f5e9;
        }

        /* 4: Sangat Baik (Blue) */
        .rating-item:nth-child(4) .rating-input:checked+.rating-label {
            border-color: #007bff;
            background-color: #e6f2ff;
        }
    </style>
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white sticky-top shadow-sm">
            <div class="container">
                <a href="/" class="navbar-brand">
                    <span class="brand-text font-weight-light">PMB Pascasarjana ULM</span>
                </a>
            </div>
        </nav>

        <div class="content-wrapper">
            <div class="content pt-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="text-center mb-4">
                                <h2 class="font-weight-bold text-dark"><?php echo htmlspecialchars($survey['title']); ?>
                                </h2>
                                <p class="text-muted" style="font-size: 1.1em;">
                                    <?php echo htmlspecialchars($survey['description']); ?></p>
                            </div>

                            <form action="/survey/submit/<?php echo $survey['id']; ?>" method="POST">
                                <div class="card card-primary card-outline shadow-sm border-0">
                                    <div class="card-body">
                                        <?php
                                        $qNum = 1;
                                        foreach ($groupedQuestions as $category => $questions):
                                            ?>
                                            <?php if ($hasCategories): ?>
                                                <div class="alert alert-light border-left border-primary mt-4 mb-3 bg-light">
                                                    <h5 class="m-0 text-primary font-weight-bold"><i
                                                            class="fas fa-tag mr-2"></i>
                                                        <?php echo htmlspecialchars($category); ?></h5>
                                                </div>
                                            <?php endif; ?>

                                            <?php foreach ($questions as $q): ?>
                                                <div class="question-box mb-5 p-3 rounded" style="background: #fff;">
                                                    <div class="mb-3">
                                                        <label class="font-weight-bold text-dark d-block"
                                                            style="font-size: 1.15em;">
                                                            <span class="badge badge-primary mr-2"
                                                                style="font-size: 0.9em; vertical-align: text-top;"><?php echo $qNum++; ?></span>
                                                            <?php echo htmlspecialchars($q['question_text']); ?> <span
                                                                class="text-danger">*</span>
                                                        </label>
                                                    </div>

                                                    <!-- Emoji Rating Interface -->
                                                    <div class="rating-container">
                                                        <!-- Option 1: Tidak Baik -->
                                                        <div class="rating-item">
                                                            <input type="radio" name="answers[<?php echo $q['id']; ?>]"
                                                                id="q<?php echo $q['id']; ?>_1" value="1" class="rating-input"
                                                                required>
                                                            <label for="q<?php echo $q['id']; ?>_1" class="rating-label">
                                                                <span class="rating-icon">😫</span>
                                                                <span class="rating-text">Tidak Sesuai</span>
                                                            </label>
                                                        </div>

                                                        <!-- Option 2: Kurang Baik -->
                                                        <div class="rating-item">
                                                            <input type="radio" name="answers[<?php echo $q['id']; ?>]"
                                                                id="q<?php echo $q['id']; ?>_2" value="2" class="rating-input">
                                                            <label for="q<?php echo $q['id']; ?>_2" class="rating-label">
                                                                <span class="rating-icon">😐</span>
                                                                <span class="rating-text">Kurang Sesuai</span>
                                                            </label>
                                                        </div>

                                                        <!-- Option 3: Baik -->
                                                        <div class="rating-item">
                                                            <input type="radio" name="answers[<?php echo $q['id']; ?>]"
                                                                id="q<?php echo $q['id']; ?>_3" value="3" class="rating-input">
                                                            <label for="q<?php echo $q['id']; ?>_3" class="rating-label">
                                                                <span class="rating-icon">😊</span>
                                                                <span class="rating-text">Sesuai</span>
                                                            </label>
                                                        </div>

                                                        <!-- Option 4: Sangat Baik -->
                                                        <div class="rating-item">
                                                            <input type="radio" name="answers[<?php echo $q['id']; ?>]"
                                                                id="q<?php echo $q['id']; ?>_4" value="4" class="rating-input">
                                                            <label for="q<?php echo $q['id']; ?>_4" class="rating-label">
                                                                <span class="rating-icon">😍</span>
                                                                <span class="rating-text">Sangat Sesuai</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="border-light">
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>

                                        <div class="form-group mt-4 p-3 bg-light rounded border">
                                            <label for="suggestion" class="font-weight-bold text-dark"><i
                                                    class="fas fa-comment-dots mr-2 text-info"></i> Saran & Masukan
                                                (Opsional)</label>
                                            <textarea class="form-control border-0 shadow-none" name="suggestion"
                                                rows="3"
                                                placeholder="Tuliskan saran Anda untuk perbaikan layanan kami..."
                                                style="background: transparent;"></textarea>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center bg-white border-0 pb-5">
                                        <button type="submit"
                                            class="btn btn-primary btn-lg px-5 shadow rounded-pill font-weight-bold transition-all hover-lift">
                                            <i class="fas fa-paper-plane mr-2"></i> KIRIM SURVEI
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer text-center border-0 mt-4 bg-transparent">
            <strong class="text-dark">&copy; <?php echo date('Y'); ?> Panitia PMB Pascasarjana ULM.</strong> <br>
            <small class="text-muted">Survei Kepuasan Masyarakat berdasarkan PermenPAN-RB No 14 Tahun 2017</small>
        </footer>
    </div>
</body>

</html>