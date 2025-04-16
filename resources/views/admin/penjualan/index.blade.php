@extends('layouts.admin')

@section('main-content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Penjualan</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        <script>
            // Clear the local storage and reload the page after a successful transaction
            localStorage.removeItem("cart");
            window.location.reload();
        </script>
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
                <label for="searchBarang">Cari Barang</label>
                <div class="input-group">
                    <input type="text" name="query" class="form-control" placeholder="Cari barang berdasarkan kode atau nama" value="{{ request('query') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="searchProducts()">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
    <form id="barcodeForm">
                <label for="searchBarang">Cari Barang</label>
                <div class="input-group">
                    <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode di sini..." autofocus>
                </div>
            </form>
        </div>
    </div>



    <!-- Form Pencarian Member -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <label for="searchMember">Cari Member</label>
            <div class="input-group">
                <input type="text" id="searchMember" class="form-control" placeholder="Masukkan nama atau ID member">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="searchMember()">Cari</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Transaksi -->
    <form action="{{ route('admin.penjualan.store') }}" method="post" id="transactionForm">
        @csrf
        <input type="hidden" id="selectedMemberId" name="id_member">
        <!-- Keranjang Belanja -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Keranjang Belanja</h6>
            </div>
            <div class="card-body" id="cartItems">
                <p class="text-center">Keranjang masih kosong.</p>
            </div>
            <div class="card-footer text-right">
                <!-- Input tersembunyi akan ditambahkan secara dinamis oleh JavaScript -->
                <div class="form-group">
                    <label for="cash">Uang Cash</label>
                    <input type="text" class="form-control" id="cash" name="cash" placeholder="Masukkan jumlah uang cash" required>
                </div>
                <div class="form-group">
                    <label for="change">Kembalian</label>
                    <input type="text" class="form-control" id="change" name="change" readonly>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </div>
    </form>

    <!-- Modal untuk Hasil Pencarian -->
    <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
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

    <div class="modal fade" id="searchMemberModal" tabindex="-1" role="dialog" aria-labelledby="searchMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchMemberModalLabel">Hasil Pencarian Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="searchMemberResults">
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

    // Fungsi untuk format angka menjadi mata uang Rupiah
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
    }

    function searchMember() {
        let query = document.getElementById('searchMember').value;
        if (!query) {
            alert("Masukkan nama atau ID member.");
            return;
        }

        fetch(`/admin/penjualan/search-member?query=${query}`)
            .then(response => response.json())
            .then(data => {
                let searchMemberResults = document.getElementById('searchMemberResults');
                searchMemberResults.innerHTML = '';

                if (data.length === 0) {
                    searchMemberResults.innerHTML = '<p class="text-center">Member tidak ditemukan.</p>';
                } else {
                    data.forEach(member => {
                        let memberCard = document.createElement('div');
                        memberCard.classList.add('card', 'mb-3');
                        memberCard.innerHTML = `
                            <div class="card-body">
                                <h5 class="card-title">${member.nama_pelanggan}</h5>
                                <p class="card-text">
                                    <strong>No:</strong> ${member.no_telp}<br>
                                    <strong>Kode Pelanggan:</strong> ${member.kode_pelanggan}
                                </p>
                                <button class="btn btn-success btn-sm" onclick="selectMember(${member.id}, '${member.nama_pelanggan}')">
                                    Pilih Member
                                </button>
                            </div>
                        `;
                        searchMemberResults.appendChild(memberCard);
                    });
                }

                $('#searchMemberModal').modal('show');
            })
            .catch(error => console.error("Error fetching members:", error));
    }

    window.addEventListener('load', () => {
        document.getElementById('barcodeInput').focus();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            document.getElementById('barcodeInput').focus();
        }
    });

    document.getElementById('barcodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let barcode = document.getElementById('barcodeInput').value.trim();
    if (!barcode) return;

    fetch('/admin/penjualan/search?query=' + encodeURIComponent(barcode))
        .then(res => res.json())
        .then(results => {
            if (results.length > 0) {
                let product = results[0]; // Ambil yang pertama cocok
                let existing = cart.find(p => p.id == product.id);
                if (existing) {
                    existing.quantity++;
                } else {
                    cart.push({
                        id: product.id,
                        nama_barang: product.nama_barang,
                        harga: product.harga_beli,
                        gambar: product.gambar_barang,
                        quantity: 1
                    });
                }

                saveCart();
                renderCart();
                document.getElementById('barcodeInput').value = '';
            } else {
                alert("Produk tidak ditemukan.");
                document.getElementById('barcodeInput').select();
            }
        })
        .catch(error => {
            console.error('Error saat mencari produk:', error);
        });
    });

    function selectMember(id, nama) {
        document.getElementById('selectedMemberId').value = id;
        document.getElementById('searchMember').value = nama;
        $('#searchMemberModal').modal('hide');
    }

    // Fungsi untuk memformat input uang cash
    function formatInputCurrency(input) {
        let value = input.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = 'Rp ' + rupiah;
    }

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
                                    <strong>Harga:</strong> ${formatCurrency(product.harga_beli * (1 + product.persentase_keuntungan / 100))}
                                </p>
                                <p class="card-text">
                                    <strong>Stok:</strong> ${product.stok}
                                </p>
                                <input type="number" id="modal-stok-${product.id}" class="form-control text-center mb-2" placeholder="Masukkan jumlah" min="1" max="${product.stok}" pattern="\d*">
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
                        <small class="text-muted">${formatCurrency(product.harga)} x ${product.quantity} = ${formatCurrency(totalHargaItem)}</small>
                    </div>
                </div>
                <div>
                    <input type="number" min="1" max="${product.stok}" class="form-control form-control-sm d-inline w-25" value="${product.quantity}" onchange="updateQuantity(${product.id}, this.value)" pattern="\d*">
                    <button class="btn btn-danger btn-sm ml-2" onclick="removeFromCart(${product.id})">Hapus</button>
                </div>
            `;
            cartItemsContainer.appendChild(cartItem);
        });

        let totalDiv = document.createElement("div");
        totalDiv.classList.add("text-right", "font-weight-bold", "mt-3");
        totalDiv.innerHTML = `<h5>Total Keseluruhan: ${formatCurrency(total)}</h5>`;
        cartItemsContainer.appendChild(totalDiv);

        // Update the change field
        document.getElementById('cash').addEventListener('input', function() {
            let cash = parseFloat(this.value.replace(/[^,\d]/g, ''));
            let change = cash - total;
            document.getElementById('change').value = formatCurrency(change > 0 ? change : 0);
        });
    }

    // Fungsi untuk memperbarui jumlah barang di keranjang
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

    // Fungsi untuk menghapus barang dari keranjang
    function removeFromCart(id) {
        cart = cart.filter(product => product.id !== id);
        saveCart();
        renderCart();
    }

    // Saat halaman dimuat, tampilkan keranjang yang tersimpan di Local Storage
    document.addEventListener("DOMContentLoaded", renderCart);

    // Saat form disubmit, tambahkan input tersembunyi untuk data keranjang
    document.getElementById('transactionForm').addEventListener('submit', function(event) {
        if (cart.length === 0) {
            alert("Keranjang belanja kosong. Silakan tambahkan barang terlebih dahulu.");
            event.preventDefault(); // Hentikan submit form
            return;
        }

        // Kosongkan input tersembunyi sebelumnya (jika ada)
        document.querySelectorAll('input[name="id_barang[]"], input[name="jumlah[]"], input[name="harga_jual[]"]').forEach(input => input.remove());

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

        // Convert cash input to integer
        let cashInput = document.getElementById('cash');
        cashInput.value = parseInt(cashInput.value.replace(/[^,\d]/g, ''));
    });

    // Tambahkan event listener untuk memformat input uang cash
    document.getElementById('cash').addEventListener('input', function() {
        formatInputCurrency(this);
    });
</script>
@endsection
