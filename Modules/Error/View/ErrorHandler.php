<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>

        ::-webkit-scrollbar, ::-webkit-scrollbar-corner {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgb(209, 204, 224);
            border-radius: 2px;
        }

        ::-webkit-scrollbar-track {
            background-color: rgb(255, 255, 255);
        }

        * {
            padding: 0;
            margin: 0;
        }

        body {
            background: #eeeef5;
            color: #68788c;
            font-family: system-ui, sans-serif;
            padding: 2rem 2rem;
        }

        header {
            background: #fff;
            margin-bottom: 1rem;
        }

        .container .cont-rem {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .container {
            border: solid 1px #e8e5ef;
        }

        .card-header {
            border-bottom: solid 1px #e8e5ef;
            padding: 5px 3px;
        }

        .card-header h1 {
            font-weight: 400;
            font-size: 12px;
            padding: 4px 0px;
        }

        .card-details {
            padding: 2rem 3rem;
            font-size: 1.2rem;
        }

        .card-details .error-class {
            opacity: 0.75;
            padding-bottom: 4px;
        }

        .card-details .error-message {
            color: black;
            font-weight: 600;
            line-height: 1.25;
            word-wrap: break-word;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 5;
            overflow: hidden;
            padding-bottom: 12px;
        }

        .link-site {
            display: inline-block;
            line-height: 1.25;
            font-size: 0.875rem;
            font-weight: 400;
            text-decoration: underline;
            color: rgba(30, 20, 70, 0.5);
        }

        .link-site:hover {
            color: rgba(15, 10, 60, 0.75);
            text-decoration-color: #1e145a59;
        }

        main {
            padding-bottom: 4rem;
        }

        .error {
            background: #DC143C;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            padding: 10px;
        }

        .code {
            padding-left: 90px;
            background: #fff;
            overflow: scroll;
            height: 350px;
        }

        .line {
            padding-left: 0.5rem;
            border-right: 1px solid rgb(238, 238, 245);
            padding-right: 0.5rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            color: #1e144680;
            background: rgb(247, 247, 252);
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .highlighted {
            font-weight: 800;
            background: rgba(255, 3, 49, 0.37);
        }

        .load-files {
            position: absolute;
            float: left;
            background: #fff;
            overflow: scroll;
            height: 350px;
            width: 280px;
        }

        .number {
            padding: 0.5rem;
            color: rgb(121, 0, 245);
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
            text-align: center;
            font-size: 12px;
        }

        .files {
            display: grid;
            align-items: center;
            grid-gap: 0.5rem;
            border-left-width: 2px;
            padding: 0.5rem;
            border-color: rgb(214, 188, 250);
            color: rgb(75, 71, 109);
            white-space: nowrap;
            font-size: 12px;
        }

        .file-list {
            width: 100%;
            background-color: rgb(251, 245, 255);
            display: grid;
            align-items: flex-end;
            grid-template-columns: 2rem auto auto;
            border-bottom: 1px solid rgb(220 220 220);
        }

        pre {
            font-size:12px;
            margin:0;
            padding:0;
        }
    </style>
    <title>🧨 <?php echo htmlspecialchars($error['message'], ENT_COMPAT, 'UTF-8', false); ?></title>
</head>
<body>
<div class="error">
    <?php echo 'Type: ' . $error['type'] . ', '; ?>

    <?php if (isset($error['code'])): ?>
        Code: <span style="color:#e1e1e1;padding:0">[<?php echo $error['code']; ?>]</span>
    <?php endif; ?>
</div>
<header class="container">

    <div class="card-details">
        <div class="error-class">
            <?php echo $error['file']; ?>
        </div>
        <div class="error-message">
            <?php echo htmlspecialchars($error['message'], ENT_COMPAT, 'UTF-8', false); ?>
        </div>
        <a href="/" target="_blank" class="link-site">
            http://127.0.0.1:8000/
        </a>
    </div>
</header>

<main>
    <div class="load-files">
        <ul>
            <?php foreach (get_included_files() as $k => $v): ?>
                <li class="file-list">
                    <div class="number">
                        <?php echo $k + 1; ?>

                    </div>
                        <div class="files">
                            <?php echo $v; ?>
                        </div>
                </li>

            <?php endforeach; ?>
        </ul>
    </div>
    <div class="code">
        <?php if (!empty($error['highlighted'])): ?>
            <?php foreach ($error['highlighted'] as $line): ?>
                <pre<?php if ($line['highlighted']): ?> class="highlighted"<?php endif; ?>>
                            <span class="line"><?php echo $line['number']; ?></span> <?php echo $line['code']; ?></pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
