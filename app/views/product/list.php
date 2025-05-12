<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <title>Product List</title>
</head>
<body>
    <div class="container"> 
        <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom"> 
            <div class="col-md-3 mb-2 mb-md-0"> 
                <a href="/Default" class="d-inline-flex link-body-emphasis text-decoration-none"> 
                    <svg class="bi" width="40" height="32" role="img" aria-label="Bootstrap"><use xlink:href="#bootstrap"></use></svg> </a> 
            </div> 
            <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0"> 
                <h1>Product List</h1>
            </ul> 
            <div class="col-md-3 text-end"> 
                <a class="btn btn-primary" href="/Product/add">Add New Product</a>
            </div> 
        </header>
    </div>
    
    <div class="container">
        <ul>
            <?php foreach ($products as $product): ?>
                <li class="d-flex flex-wrap nav col-12 col-md-auto mb-2">
                    <h4 class="col-3"><?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p class="col-3"><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES,'UTF-8'); ?></p>
                    <p class="col-3">Price: <?php echo htmlspecialchars($product->getPrice(), ENT_QUOTES,'UTF-8'); ?></p>
                    <div class="col-md-3 text-end"> 
                        <a class="btn btn-warning" href="/Product/edit/<?php echo $product->getID(); ?>">Edit</a>
                        <a class="btn btn-danger" href="/Product/delete/<?php echo $product->getID(); ?>"onclick="return confirm('You sure?');">Delete</a>
                    </div>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>