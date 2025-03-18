@extends('layouts.admin')

@section('main-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Pembelian</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-left-danger" role="alert">
                <ul class="pl-4 my-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Form Pencarian -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form id="searchForm">
                    <div class="input-group">
                        <input type="text" name="query" class="form-control"
                            placeholder="Cari barang berdasarkan kode atau nama" value="{{ request('query') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" onclick="searchProducts()">Cari</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <form action="{{ route('admin.penjualan.store') }}" method="post" id="transactionForm">
            @csrf
            <!-- Keranjang Belanja -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Keranjang Belanja</h6>
                </div>
                <div class="card-body" id="cartItems">
                    <p class="text-center">Keranjang masih kosong.</p>
                </div>
                <div class="card-footer text-right">
                    <!-- Input tersembunyi untuk menyimpan data keranjang -->
                    <input type="hidden" name="id_barang[]" id="id_barang">
                    <input type="hidden" name="jumlah[]" id="jumlah">
                    <input type="hidden" name="harga_jual[]" id="harga_jual">
                    <div class="form-group">
                        <label for="cash">Uang Cash</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" class="form-control" id="cash" name="cash"
                                placeholder="Masukkan jumlah uang cash" oninput="formatCashInput(this); calculateChange()">
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="change">Kembalian</label>
                        <input type="text" class="form-control" id="change" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </div>
        </form>
        <!-- Modal untuk Hasil Pencarian -->
        <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel">Hasil Pencarian</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="searchResults">

                        <!-- Hasil pencarian akan ditampilkan di sini -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk Keranjang Belanja dan Pencarian -->
    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        // Fungsi untuk mencari barang dan menampilkan hasil dalam modal
        function searchProducts() {
            let query = document.querySelector('input[name="query"]').value;
            fetch(`/admin/penjualan/search?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    let searchResults = document.getElementById('searchResults');
                    searchResults.innerHTML = '';

                    if (data.length === 0) {
                        searchResults.innerHTML = '<p class="text-center">Tidak ada hasil ditemukan.</p>';
                    } else {
                        data.forEach(product => {
                            let productCard = document.createElement('div');
                            productCard.classList.add('card', 'mb-3');
                            productCard.innerHTML = `
        <div class="card-body">
            <h5 class="card-title">${product.nama_barang}</h5>
            <p class="card-text">
                <strong>Harga:</strong> Rp ${(product.harga_beli * (1 + product.persentase_keuntungan / 100)).toLocaleString('id-ID')}
            </p>
            <p class="card-text">
                <strong>Stok:</strong> ${product.stok}
            </p>
            <input type="number" id="modal-stok-${product.id}" class="form-control text-center mb-2" placeholder="Masukkan jumlah" min="1" max="${product.stok}">
<button class="btn btn-success btn-sm" onclick="addToCartFromModal(${product.id}, '${product.nama_barang}', ${product.harga_beli}, ${product.persentase_keuntungan}, ${product.stok}, '${product.gambar_barang}')">
    Tambahkan ke Keranjang
</button>

        </div>
    `;
                            searchResults.appendChild(productCard);
                        });
                    }

                    $('#searchModal').modal('show');
                });
        }

        function formatCashInput(input) {
            let value = input.value.replace(/\D/g, '');
            input.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        }


        // Fungsi untuk menambahkan barang ke keranjang dari modal
        function addToCartFromModal(id, nama_barang, harga_beli, persentase_keuntungan, stok, gambar) {
            let quantityInput = document.getElementById(`modal-stok-${id}`).value;
            let quantity = parseInt(quantityInput);

            if (!quantity || quantity < 1) {
                alert("Masukkan jumlah yang valid!");
                return;
            }

            if (quantity > stok) {
                alert("Stok tidak mencukupi!");
                return;
            }

            let harga = harga_beli * (1 + persentase_keuntungan / 100);

            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                if (existingItem.quantity + quantity <= stok) {
                    existingItem.quantity += quantity;
                } else {
                    alert("Stok tidak mencukupi!");
                    return;
                }
            } else {
                cart.push({
                    id: id,
                    nama_barang: nama_barang,
                    harga: harga,
                    stok: stok,
                    quantity: quantity,
                    gambar: gambar
                });
            }

            saveCart();
            renderCart();
            $('#searchModal').modal('hide');
        }
        // Fungsi untuk menyimpan keranjang ke Local Storage
        function saveCart() {
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        // Fungsi untuk menampilkan keranjang
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
                cartItem.classList.add("d-flex", "justify-content-between", "align-items-center", "border-bottom",
                    "pb-2", "mb-2");

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

        function calculateChange() {
            let total = cart.reduce((sum, product) => sum + (product.harga * product.quantity), 0);
            let cash = parseFloat(document.getElementById('cash').value) || 0;
            let change = cash - total;
            document.getElementById('change').value = `Rp ${change.toLocaleString('id-ID')}`;
        }

        document.getElementById('transactionForm').addEventListener('submit', function(event) {
            if (cart.length === 0) {
                alert("Keranjang belanja kosong. Silakan tambahkan barang terlebih dahulu.");
                event.preventDefault(); // Hentikan submit form
                return;
            }

            // Kosongkan input tersembunyi sebelumnya (jika ada)
            document.querySelectorAll(
                'input[name="id_barang[]"], input[name="jumlah[]"], input[name="harga_jual[]"]').forEach(
                input => input.remove());

            // Loop melalui keranjang dan tambahkan data ke input tersembunyi
            cart.forEach(product => {
                // Buat elemen input baru untuk setiap item
                let idBarangInput = document.createElement('input');
                idBarangInput.type = 'hidden';
                idBarangInput.name = 'id_barang[]';
                idBarangInput.value = product.id;
                document.getElementById('transactionForm').appendChild(idBarangInput);

                let jumlahInput = document.createElement('input');
                jumlahInput.type = 'hidden';
                jumlahInput.name = 'jumlah[]';
                jumlahInput.value = product.quantity;
                document.getElementById('transactionForm').appendChild(jumlahInput);

                let hargaJualInput = document.createElement('input');
                hargaJualInput.type = 'hidden';
                hargaJualInput.name = 'harga_jual[]';
                hargaJualInput.value = product.harga;
                document.getElementById('transactionForm').appendChild(hargaJualInput);
            });
        });

        // Saat halaman dimuat, tampilkan keranjang yang tersimpan di Local Storage
        document.addEventListener("DOMContentLoaded", renderCart);
    </script>
@endsection
