@extends('layouts.app')

@section('title', 'Kasir POS - Happay')

@section('content')
<style>
    /* =========================================================
       POS RESPONSIVE (Desktop / iPad / HP)
       - Desktop & iPad : 2 kolom, tampilan di-scale 80%
       - HP (<768px)    : 1 kolom, keranjang jadi bottom-sheet
       Ubah --pos-top-offset kalau di HP ada header/navbar
       dari layouts.app yang memakan tinggi layar.
       ========================================================= */
    :root { --pos-top-offset: 0px; }

    .pos-root { position: relative; width: 100%; height: 100%; overflow: hidden; background: #f1f5f9; }

    /* Scaler 80%  (100 / 0.8 = 125%) */
    .pos-scaler {
        position: absolute; top: 0; left: 0;
        width: 125%; height: 125%;
        transform: scale(0.8); transform-origin: top left;
        display: flex; overflow: hidden; background: #f1f5f9;
    }

    .pos-catalog { flex: 1; min-width: 0; min-height: 0; display: flex; flex-direction: column;
                   border-right: 1px solid #e2e8f0; background: rgba(248,250,252,.5); }
    .pos-topbar  { display: flex; align-items: center; justify-content: space-between; gap: 16px;
                   padding: 12px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
    .pos-search  { width: 288px; flex-shrink: 0; }
    .pos-grid-wrap { flex: 1; min-height: 0; overflow-y: auto; padding: 16px; -webkit-overflow-scrolling: touch; }
    .pos-grid    { display: grid; gap: 12px; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); }

    .pos-cart    { width: 320px; flex-shrink: 0; min-height: 0; display: flex; flex-direction: column;
                   background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,.1); }
    .pos-cart-list { flex: 1; min-height: 0; overflow-y: auto; padding: 12px; -webkit-overflow-scrolling: touch; }

    /* Tombol qty (produk & keranjang) */
    .pos-qb  { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
    .pos-cqb { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
    .pos-addbtn { height: 24px; }

    /* Elemen khusus HP: disembunyikan di desktop/iPad */
    .pos-cart-bar, .pos-backdrop, .pos-cart-close { display: none; }

    /* ---------- iPad / tablet (768 - 1023px) ---------- */
    @media (min-width: 768px) and (max-width: 1023px) {
        .pos-cart { width: 290px; }
        .pos-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        .pos-search { width: 220px; }
        /* target sentuh sedikit lebih besar untuk jari */
        .pos-qb  { width: 28px; height: 28px; }
        .pos-cqb { width: 24px; height: 24px; }
        .pos-addbtn { height: 28px; }
    }

    /* ---------- HP (<= 767px): iPhone 12 mini 375px, Android 360-412px ---------- */
    @media (max-width: 767px) {
        .pos-root { height: calc(100vh - var(--pos-top-offset)); height: calc(100dvh - var(--pos-top-offset)); }

        /* Tidak di-scale di HP */
        .pos-scaler { position: relative; width: 100%; height: 100%; transform: none; }

        .pos-topbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 10px 12px; }
        .pos-search { width: 100%; }

        /* font-size 16px supaya iOS Safari tidak auto-zoom saat fokus input */
        .pos-root input[type="text"], .pos-root select { font-size: 16px !important; }

        .pos-grid-wrap { padding: 10px 10px 96px; }
        .pos-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }

        .pos-qb  { width: 32px; height: 32px; font-size: 14px; }
        .pos-cqb { width: 30px; height: 30px; font-size: 14px; }
        .pos-addbtn { height: 32px; }

        /* Keranjang = bottom sheet */
        .pos-cart {
            position: fixed; left: 0; right: 0; bottom: 0;
            width: 100%; height: 88vh; height: 88dvh;
            border-radius: 20px 20px 0 0; z-index: 60;
            transform: translateY(105%); transition: transform .3s ease;
            box-shadow: 0 -10px 30px rgba(0,0,0,.2);
        }
        .pos-cart.open { transform: translateY(0); }
        .pos-cart-close { display: inline-flex; }
        .pos-cart-foot { padding-bottom: calc(12px + env(safe-area-inset-bottom)) !important; }

        .pos-backdrop { display: block; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 55; }

        /* Bar total melayang di bawah */
        .pos-cart-bar {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: #4f46e5; color: #fff; box-shadow: 0 -4px 16px rgba(0,0,0,.15);
            border: 0; width: 100%; text-align: left;
        }
    }

    /* HP kecil (<= 360px) */
    @media (max-width: 360px) {
        .pos-grid-wrap { padding: 8px 8px 96px; }
        .pos-grid { gap: 8px; }
    }
</style>

<div class="pos-root"
     x-data="posSystem()"
     x-init="initPos()"
     x-effect="document.body.style.overflow = cartOpen ? 'hidden' : ''">
<div class="pos-scaler">

    <!-- ================= KIRI: KATALOG PRODUK ================= -->
    <div class="pos-catalog">

        <!-- Top Bar: Warehouse & Search -->
        <div class="pos-topbar shadow-sm">
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Aktif Cabang / Gudang</span>
                <div class="text-sm font-bold text-slate-900">
                    @if(count($warehouses) === 1)
                        <span class="truncate block">[{{ $warehouses->first()->region->region_alias ?? 'N/A' }}] {{ $warehouses->first()->warehouse_name }}</span>
                        <input type="hidden" id="active_warehouse_id" value="{{ $warehouses->first()->id }}">
                    @else
                        <select id="active_warehouse_id" @change="fetchStocks()" class="mt-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold bg-slate-50 focus:outline-none max-w-full w-full">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ ($defaultWarehouseId ?? '') == $wh->id ? 'selected' : '' }}>
                                    [{{ optional($wh->region)->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="pos-search">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Cari nama produk / SKU..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
            </div>
        </div>

        <!-- Product Grid -->
        <div class="pos-grid-wrap">
            <div class="pos-grid">
                <template x-for="product in filteredProducts" :key="product.stock_master_id">
                    <div class="min-w-0 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition p-3 flex flex-col group">

                        <!-- Gambar -->
                        <div class="relative w-full rounded-xl bg-slate-100 overflow-hidden mb-2.5 border border-slate-100" style="aspect-ratio:4/3;">
                            <template x-if="product.item_image">
                                <img :src="'/storage/' + product.item_image" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </template>
                            <template x-if="!product.item_image">
                                <div class="w-full h-full flex items-center justify-center text-[10px] text-slate-400 font-bold">No Image</div>
                            </template>
                            <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md text-[10px] font-bold font-mono bg-slate-900/70 text-white backdrop-blur-sm">
                                Stok: <span x-text="product.stock_now"></span>
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="mb-2.5 min-w-0 flex-1">
                            <span class="text-[10px] font-mono text-indigo-600 font-bold block truncate" x-text="product.item_sku"></span>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-2 mt-0.5 leading-snug" x-text="product.item_name"></h4>
                            <p class="text-[11px] text-slate-500 truncate" x-text="product.item_variant || 'Standard'"></p>
                            <p class="text-xs font-mono font-bold text-slate-900 mt-1" x-text="formatRupiah(product.item_price)"></p>
                        </div>

                        <!-- Qty + Add to Cart -->
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                            <div class="flex items-center bg-slate-100 rounded-lg border border-slate-200 shrink-0">
                                <button type="button" @click="decrementQty(product)" class="pos-qb text-slate-600 hover:bg-white rounded-l-lg font-bold text-xs transition">&minus;</button>
                                <span class="text-center text-xs font-bold text-slate-800" style="min-width:22px;" x-text="product.temp_qty || 1"></span>
                                <button type="button" @click="incrementQty(product)" class="pos-qb text-slate-600 hover:bg-white rounded-r-lg font-bold text-xs transition">+</button>
                            </div>

                            <button type="button" @click="addToCart(product)" title="Tambah ke keranjang"
                                    class="pos-addbtn flex-1 min-w-0 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg shadow-sm shadow-indigo-600/20 transition flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.3 2.6A1 1 0 006.6 17H19M9 20.5a1 1 0 11-2 0 1 1 0 012 0zm9 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v4M10 9h4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Backdrop (HP) -->
    <div class="pos-backdrop" x-show="cartOpen" x-transition.opacity @click="cartOpen = false" style="display:none;"></div>

    <!-- ================= KANAN: KERANJANG (HP = bottom sheet) ================= -->
    <div class="pos-cart" :class="{ 'open': cartOpen }">

        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
            <h2 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span>🛒 Keranjang</span>
                <span class="bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded-full font-bold" x-text="cart.length"></span>
            </h2>
            <div class="flex items-center gap-3">
                <button type="button" @click="clearCart()" class="text-xs font-semibold text-rose-500 hover:text-rose-700">Kosongkan</button>
                <button type="button" @click="cartOpen = false" class="pos-cart-close items-center justify-center text-slate-500 text-lg font-bold" style="width:32px;height:32px;" aria-label="Tutup">&times;</button>
            </div>
        </div>

        <!-- List -->
        <div class="pos-cart-list space-y-2">
            <template x-for="(item, index) in cart" :key="index">
                <div class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition">
                    <div class="w-10 h-10 rounded-lg bg-slate-200 shrink-0 overflow-hidden border border-slate-200">
                        <template x-if="item.item_image">
                            <img :src="'/storage/' + item.item_image" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!item.item_image">
                            <div class="w-full h-full flex items-center justify-center text-[9px] text-slate-400 font-bold">Img</div>
                        </template>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h5 class="text-xs font-bold text-slate-900 truncate" x-text="item.item_name"></h5>
                        <p class="text-[10px] text-slate-500 truncate" x-text="item.item_variant || 'Standard'"></p>
                        <div class="text-xs font-mono font-bold text-indigo-600 mt-0.5" x-text="formatRupiah(item.price * item.qty)"></div>
                    </div>

                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <button type="button" @click="removeFromCart(index)" class="text-slate-400 hover:text-rose-600 text-base leading-none font-bold px-1">&times;</button>
                        <div class="flex items-center bg-white rounded-md border border-slate-200">
                            <button type="button" @click="updateCartQty(index, -1)" class="pos-cqb text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-l-md">&minus;</button>
                            <span class="text-center text-xs font-bold text-slate-800" style="min-width:20px;" x-text="item.qty"></span>
                            <button type="button" @click="updateCartQty(index, 1)" class="pos-cqb text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-r-md">+</button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="cart.length === 0">
                <div class="py-16 text-center text-slate-400 text-xs">
                    Belum ada produk yang dipilih.
                </div>
            </template>
        </div>

        <!-- Checkout -->
        <div class="pos-cart-foot p-3 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <form action="{{ route('pos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="warehouse_id" :value="selectedWarehouseId">

                <template x-for="(item, index) in cart" :key="'f' + index">
                    <div>
                        <input type="hidden" :name="`items[${index}][stock_master_id]`" :value="item.stock_master_id">
                        <input type="hidden" :name="`items[${index}][qty]`" :value="item.qty">
                        <input type="hidden" :name="`items[${index}][price]`" :value="item.price">
                    </div>
                </template>

                <div class="space-y-1.5 mb-3 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-mono font-bold text-slate-900" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Pajak (11%)</span>
                        <span class="font-mono font-bold text-slate-900" x-text="formatRupiah(tax)"></span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-200 font-bold text-slate-900">
                        <span>Total Akhir</span>
                        <span class="font-mono text-indigo-600" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-2.5">
                    <input type="text" name="transaction_info_name" required placeholder="Nama Pelanggan *" class="min-w-0 px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="transaction_info_number" required inputmode="tel" placeholder="No Telepon *" class="min-w-0 px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex gap-2">
                    <button type="submit" name="transaction_status" value="draft" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl text-xs font-bold transition">
                        Simpan Draft
                    </button>
                    <button type="submit" name="transaction_status" value="completed" :disabled="cart.length === 0" class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 text-white py-2.5 rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                        Bayar & Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bar total melayang (hanya tampil di HP) -->
    <button type="button" class="pos-cart-bar" @click="cartOpen = true">
        <span class="flex items-center gap-2 text-sm font-bold">
            <span>🛒</span>
            <span><span x-text="cartCount"></span> item</span>
        </span>
        <span class="flex items-center gap-2 text-sm font-bold">
            <span class="font-mono" x-text="formatRupiah(grandTotal)"></span>
            <span style="background:rgba(255,255,255,.2);padding:4px 10px;border-radius:999px;font-size:12px;">Lihat</span>
        </span>
    </button>

</div>
</div>

<!-- Script Alpine.js Controller Logic -->
<script>
function posSystem() {
    return {
        products: [],
        cart: [],
        searchQuery: '',
        selectedWarehouseId: '',
        cartOpen: false,

        initPos() {
            const whSelect = document.getElementById('active_warehouse_id');
            if (whSelect) {
                this.selectedWarehouseId = whSelect.value;
                this.fetchStocks();
            }
        },

        fetchStocks() {
            const whSelect = document.getElementById('active_warehouse_id');
            if (whSelect) {
                this.selectedWarehouseId = whSelect.value;
            }
            if (!this.selectedWarehouseId) return;

            fetch(`/pos/stocks?warehouse_id=${this.selectedWarehouseId}`)
                .then(res => res.json())
                .then(data => {
                    this.products = data.map(p => ({ ...p, temp_qty: 1 }));
                });
        },

        get filteredProducts() {
            if (!this.searchQuery) return this.products;
            const q = this.searchQuery.toLowerCase();
            return this.products.filter(p =>
                p.item_name.toLowerCase().includes(q) ||
                p.item_sku.toLowerCase().includes(q)
            );
        },

        incrementQty(product) {
            if (product.temp_qty < product.stock_now) {
                product.temp_qty++;
            }
        },

        decrementQty(product) {
            if (product.temp_qty > 1) {
                product.temp_qty--;
            }
        },

        addToCart(product) {
            const qtyToAdd = product.temp_qty || 1;
            const existingIndex = this.cart.findIndex(item => item.stock_master_id === product.stock_master_id);

            if (existingIndex > -1) {
                const newQty = this.cart[existingIndex].qty + qtyToAdd;
                if (newQty <= product.stock_now) {
                    this.cart[existingIndex].qty = newQty;
                } else {
                    alert('Stok tidak mencukupi!');
                }
            } else {
                this.cart.push({
                    stock_master_id: product.stock_master_id,
                    item_name: product.item_name,
                    item_variant: product.item_variant,
                    item_image: product.item_image,
                    price: product.item_price,
                    qty: qtyToAdd,
                    max_stock: product.stock_now
                });
            }
            product.temp_qty = 1;
        },

        updateCartQty(index, change) {
            const item = this.cart[index];
            const newQty = item.qty + change;
            if (newQty > 0 && newQty <= item.max_stock) {
                item.qty = newQty;
            } else if (newQty <= 0) {
                this.removeFromCart(index);
            } else {
                alert('Stok maksimum tercapai!');
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            this.cart = [];
        },

        get cartCount() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get tax() {
            return this.subtotal * 0.11;
        },

        get grandTotal() {
            return this.subtotal + this.tax;
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }
    }
}
</script>
@endsection