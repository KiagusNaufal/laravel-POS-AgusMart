@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Penjualan</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Form Pencarian -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.penjualan') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="query" class="form-control" placeholder="Cari barang berdasarkan kode atau nama" value="{{ request('query') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Keranjang Belanja -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Keranjang Belanja</h6>
        </div>
        <div class="card-body" id="cartItems">
            <p class="text-center">Keranjang masih kosong.</p>
        </div>
    </div>

    <!-- Daftar Barang -->
    <div class="row">
        @foreach($barang as $product)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow">
                <img src="{{ asset('images/' . $product->gambar_barang) }}" class="card-img-top img-fluid" alt="{{ $product->nama_barang }}" style="height: 200px; object-fit: cover;">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $product->nama_barang }}</h5>
                    <p class="card-text">
                        <strong>Harga:</strong> Rp {{ number_format($product->harga_beli * (1 + $product->persentase_keuntungan / 100), 0, ',', '.') }}
                    </p>
                    <p class="card-text">
                        <strong>Stok:</strong> {{ $product->stok }}
                    </p>
                    <input type="number" id="stok-{{ $product->id }}" class="form-control text-center mb-2" placeholder="Masukkan jumlah" min="1" max="{{ $product->stok }}">
                </div>
                <div class="card-footer bg-white text-center">
                    <button class="btn btn-success btn-sm" onclick="addToCart({{ json_encode($product) }})">
                        Tambahkan ke Keranjang
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Script untuk Keranjang Belanja -->
<script>
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    function addToCart(product) {
        let quantityInput = document.getElementById(`stok-${product.id}`).value;
        let quantity = parseInt(quantityInput);

        if (!quantity || quantity < 1) {
            alert("Masukkan jumlah yang valid!");
            return;
        }

        if (quantity > product.stok) {
            alert("Stok tidak mencukupi!");
            return;
        }

        let existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if (existingItem.quantity + quantity <= product.stok) {
                existingItem.quantity += quantity;
            } else {
                alert("Stok tidak mencukupi!");
                return;
            }
        } else {
            cart.push({
                id: product.id,
                nama_barang: product.nama_barang,
                gambar: product.gambar_barang,
                harga: product.harga_beli * (1 + product.persentase_keuntungan / 100),
                stok: product.stok,
                quantity: quantity
            });
        }
        saveCart();
        renderCart();
    }

    function updateQuantity(id, newQuantity) {
        let item = cart.find(product => product.id === id);
        if (item) {
            if (newQuantity <= item.stok && newQuantity > 0) {
                item.quantity = parseInt(newQuantity);
            } else {
                alert("Jumlah melebihi stok yang tersedia atau kurang dari 1!");
            }
        }
        saveCart();
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(product => product.id !== id);
        saveCart();
        renderCart();
    }

    function saveCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
    }

    function renderCart() {
        let cartItemsContainer = document.getElementById("cartItems");
        cartItemsContainer.innerHTML = "";

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = "<p class='text-center'>Keranjang masih kosong.</p>";
            return;
        }

        let total = 0;
        cart.forEach(product => {
            let totalHargaItem = product.harga * product.quantity;
            total += totalHargaItem;

            let cartItem = document.createElement("div");
            cartItem.classList.add("d-flex", "justify-content-between", "align-items-center", "border-bottom", "pb-2", "mb-2");

            cartItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="/images/${product.gambar}" style="width: 50px; height: 50px; object-fit: cover;" class="mr-3 rounded">
                    <div>
                        <h6 class="mb-0">${product.nama_barang}</h6>
                        <small class="text-muted">Rp ${product.harga.toLocaleString('id-ID')} x ${product.quantity} = Rp ${totalHargaItem.toLocaleString('id-ID')}</small>
                    </div>
                </div>
                <div>
                    <input type="number" min="1" max="${product.stok}" class="form-control form-control-sm d-inline w-25" value="${product.quantity}" onchange="updateQuantity(${product.id}, this.value)">
                    <button class="btn btn-danger btn-sm ml-2" onclick="removeFromCart(${product.id})">Hapus</button>
                </div>
            `;
            cartItemsContainer.appendChild(cartItem);
        });

        let totalDiv = document.createElement("div");
        totalDiv.classList.add("text-right", "font-weight-bold", "mt-3");
        totalDiv.innerHTML = `<h5>Total Keseluruhan: Rp ${total.toLocaleString('id-ID')}</h5>`;
        cartItemsContainer.appendChild(totalDiv);
    }

    // Saat halaman dimuat, tampilkan keranjang yang tersimpan di Local Storage
    document.addEventListener("DOMContentLoaded", renderCart);
</script>
@endsection
