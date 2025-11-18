@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>New Transaction</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="product-search" 
                                   placeholder="Search by name or PLU...">
                            <small class="text-muted">Cari dari semua produk (nama atau PLU)</small>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary" id="show-all-btn" onclick="toggleShowAll()">
                                Show All Products
                            </button>
                            <span class="badge bg-info" id="product-count">Showing 5 of {{ $products->count() }} products</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Cart items will be added here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td id="subtotal">Rp 0</td>
                                </tr>
                                <tr>
                                    <th>Tax (10%):</th>
                                    <td id="tax">Rp 0</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td id="total">Rp 0</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="processTransaction()">Process Transaction</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Products</h4>
                    <small class="badge bg-secondary" id="visible-products-count">5</small>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div id="product-list">
                        <!-- Products will be loaded by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let allProducts = {!! $products->toJson() !!};
let showAll = false;
const initialLimit = 5;
let currentSearchTerm = '';

// Initialize products on page load
document.addEventListener('DOMContentLoaded', function() {
    renderProducts();
});

// Function to render products based on current state
function renderProducts() {
    const productList = document.getElementById('product-list');
    productList.innerHTML = '';

    // Determine which products to show
    let productsToShow;
    
    if (currentSearchTerm) {
        // Jika sedang search, tampilkan semua produk yang match dengan search
        productsToShow = allProducts.filter(product => 
            product.name.toLowerCase().includes(currentSearchTerm) || 
            product.plu.includes(currentSearchTerm)
        );
    } else if (showAll) {
        // Jika show all dan tidak search, tampilkan semua
        productsToShow = allProducts;
    } else {
        // Jika tidak show all dan tidak search, tampilkan hanya 5 pertama
        productsToShow = allProducts.slice(0, initialLimit);
    }

    // Render products
    productsToShow.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'product-item mb-2 p-2 border rounded';
        productItem.setAttribute('data-product-id', product.id);
        productItem.setAttribute('data-product-name', product.name);
        productItem.setAttribute('data-product-price', product.price);
        productItem.setAttribute('data-product-plu', product.plu);
        productItem.setAttribute('data-product-stock', product.stock);
        productItem.setAttribute('onclick', `addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price})`);
        productItem.style.cursor = 'pointer';
        
        productItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <strong>${product.name}</strong>
                <small class="text-muted">PLU: ${product.plu}</small>
            </div>
            <small>Rp ${product.price.toLocaleString('id-ID')}</small>
            <br>
            <small class="text-${product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger')}">
                Stock: ${product.stock}
            </small>
        `;
        
        productList.appendChild(productItem);
    });

    updateProductCount();
    updateShowAllButton();
}

// Function to toggle show all products
function toggleShowAll() {
    // Jika sedang search, clear search dulu
    if (currentSearchTerm) {
        document.getElementById('product-search').value = '';
        currentSearchTerm = '';
    }
    
    showAll = !showAll;
    renderProducts();
}

// Function to update show all button
function updateShowAllButton() {
    const showAllBtn = document.getElementById('show-all-btn');
    if (showAll) {
        showAllBtn.textContent = 'Show Less';
        showAllBtn.classList.remove('btn-outline-secondary');
        showAllBtn.classList.add('btn-outline-primary');
    } else {
        showAllBtn.textContent = 'Show All Products';
        showAllBtn.classList.remove('btn-outline-primary');
        showAllBtn.classList.add('btn-outline-secondary');
    }
}

// Function untuk update product count
function updateProductCount() {
    const visibleCount = document.querySelectorAll('.product-item').length;
    const totalCount = allProducts.length;
    
    document.getElementById('visible-products-count').textContent = visibleCount;
    
    if (currentSearchTerm) {
        // Jika sedang search
        document.getElementById('product-count').textContent = `Found ${visibleCount} products`;
    } else if (showAll) {
        // Jika show all
        document.getElementById('product-count').textContent = `Showing all ${totalCount} products`;
    } else {
        // Jika hanya tampil 5
        document.getElementById('product-count').textContent = `Showing ${visibleCount} of ${totalCount} products`;
    }
}

function addToCart(productId, productName, price) {
    // Cek stock tersedia
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;
    
    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
        // Cek jika quantity melebihi stock
        if (existingItem.quantity + 1 > product.stock) {
            alert(`Cannot add more. Only ${product.stock} items available in stock.`);
            return;
        }
        existingItem.quantity += 1;
        existingItem.subtotal = existingItem.quantity * price;
    } else {
        // Cek stock untuk item baru
        if (product.stock < 1) {
            alert('Product out of stock');
            return;
        }
        cart.push({
            product_id: productId,
            name: productName,
            price: price,
            quantity: 1,
            subtotal: price
        });
    }
    
    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function updateQuantity(index, change) {
    const productId = cart[index].product_id;
    const product = allProducts.find(p => p.id === productId);
    
    if (!product) return;
    
    const newQuantity = cart[index].quantity + change;
    
    // Cek stock
    if (newQuantity > product.stock) {
        alert(`Cannot add more. Only ${product.stock} items available in stock.`);
        return;
    }
    
    if (newQuantity < 1) {
        removeFromCart(index);
        return;
    }
    
    cart[index].quantity = newQuantity;
    cart[index].subtotal = cart[index].quantity * cart[index].price;
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const subtotalElement = document.getElementById('subtotal');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    
    cartItems.innerHTML = '';
    
    let subtotal = 0;
    
    cart.forEach((item, index) => {
        subtotal += item.subtotal;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>Rp ${item.price.toLocaleString('id-ID')}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                ${item.quantity}
                <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, 1)">+</button>
            </td>
            <td>Rp ${item.subtotal.toLocaleString('id-ID')}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">Remove</button>
            </td>
        `;
        cartItems.appendChild(row);
    });
    
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    subtotalElement.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    taxElement.textContent = `Rp ${tax.toLocaleString('id-ID')}`;
    totalElement.textContent = `Rp ${total.toLocaleString('id-ID')}`;
}

function processTransaction() {
    if (cart.length === 0) {
        alert('Please add products to cart');
        return;
    }
    
    fetch('{{ route("transactions.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            items: cart
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Error: ' . data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the transaction');
    });
}

// Enhanced search functionality - search dari SEMUA produk
document.getElementById('product-search').addEventListener('input', function(e) {
    currentSearchTerm = e.target.value.toLowerCase().trim();
    
    // Jika search dikosongkan, kembali ke state sebelumnya
    if (!currentSearchTerm) {
        renderProducts();
        return;
    }
    
    // Search dari semua produk
    const filteredProducts = allProducts.filter(product => 
        product.name.toLowerCase().includes(currentSearchTerm) || 
        product.plu.includes(currentSearchTerm)
    );
    
    const productList = document.getElementById('product-list');
    productList.innerHTML = '';
    
    filteredProducts.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'product-item mb-2 p-2 border rounded';
        productItem.setAttribute('data-product-id', product.id);
        productItem.setAttribute('data-product-name', product.name);
        productItem.setAttribute('data-product-price', product.price);
        productItem.setAttribute('data-product-plu', product.plu);
        productItem.setAttribute('data-product-stock', product.stock);
        productItem.setAttribute('onclick', `addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price})`);
        productItem.style.cursor = 'pointer';
        
        productItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <strong>${product.name}</strong>
                <small class="text-muted">PLU: ${product.plu}</small>
            </div>
            <small>Rp ${product.price.toLocaleString('id-ID')}</small>
            <br>
            <small class="text-${product.stock > 10 ? 'success' : (product.stock > 0 ? 'warning' : 'danger')}">
                Stock: ${product.stock}
            </small>
        `;
        
        productList.appendChild(productItem);
    });
    
    updateProductCount();
});

// Quick search dengan Enter untuk PLU - search dari SEMUA produk
document.getElementById('product-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const searchTerm = e.target.value.trim();
        
        // Jika search term adalah angka (kemungkinan PLU), cari di semua products
        if (/^\d+$/.test(searchTerm)) {
            const product = allProducts.find(p => p.plu === searchTerm);
            
            if (product) {
                addToCart(product.id, product.name, product.price);
                e.target.value = ''; // Clear search
                currentSearchTerm = '';
                
                // Kembalikan ke tampilan normal
                renderProducts();
                
                // Highlight produk yang ditambahkan
                const productItem = document.querySelector(`[data-product-plu="${searchTerm}"]`);
                if (productItem) {
                    productItem.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        productItem.style.backgroundColor = '';
                    }, 1000);
                }
            } else {
                alert('Product with PLU ' + searchTerm + ' not found');
            }
        }
    }
});
</script>

<style>
.product-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.product-item {
    transition: all 0.2s ease;
}
</style>
@endsection