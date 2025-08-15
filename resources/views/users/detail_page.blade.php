@extends('layouts.luxury-nav')

@section('title', 'เมนู')

@section('content')
    <?php
    
    use App\Models\Config;
    use App\Models\Categories;
    
    $config = Config::first();
    
    $currentCategory = Categories::find($category_id ?? request()->route('id'));
    $allCategories = Categories::get();

    ?>
    <style>

.header-section {
    background: linear-gradient(135deg, {{ $config->color1 ?? '#6cd4e2ff' }} 0%, {{ $config->color2 ?? '#8fd8e0ff' }} 100%);
    padding: 15px 15px 20px 15px;
    border-radius: 0 0 25px 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    margin: -15px -15px 15px -15px;
}

.page-title {
    color: white;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.page-title i {
    font-size: 20px;
}

/* Search Section */
.search-wrapper {
    position: relative;
    margin-bottom: 12px;
}

.search-input {
    width: 100%;
    padding: 10px 45px 10px 18px;
    border: none;
    border-radius: 22px;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

.search-input::placeholder {
    color: #6c757d;
    font-size: 14px;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    font-size: 14px;
}

.clear-search {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    display: none;
}

.clear-search.show {
    display: block;
}

/* Category Pills */
.category-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.category-pills::-webkit-scrollbar {
    display: none;
}

.category-pill {
    padding: 6px 16px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 13px;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.category-pill:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.category-pill.active {
    background: white;
    color: {{ $config->color1 ?? '#00bcd4' }};
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}


.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 14px;
    margin-top: 8px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    max-height: 350px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-results.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-result-item {
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid transparent;
    padding: 10px 12px;
}

.search-result-item:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.search-result-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: bold;
    color: #856404;
}


.menu-grid {
    transition: opacity 0.3s ease;
}

.menu-grid.searching {
    opacity: 0.3;
    pointer-events: none;
}

.no-results {
    text-align: center;
    padding: 40px 20px;
}

.title-food {
    font-size: 30px;
    font-weight: bold;
    color: {{ $config->color_font ?? '#000000' }};
}

.card-food {
    background-color: var(--bg-card-food);
    border-radius: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    padding: 4px;
}

.card-title {
    font-size: 15px;
    color: {{ $config->color_font ?? '#000000' }};
}

/* Product Card */
.product-card {
    cursor: pointer;
    border-radius: 10px;
    transition: transform 0.2s ease;
}

.product-card:hover {
    transform: translateY(-2px);
}

/* Cart Amount Badge */
.cart-amount-badge {
    position: absolute;
    bottom: 5px;
    right: 20px;
    transform: translateX(50%);
    border: 1px solid {{ $config->color1 ?? '#30acff' }};
    background-color: #ffffff;
    color: rgb(0, 0, 0);
    padding: 2px 10px;
    font-size: 13px;
    border-radius: 50%;
    z-index: 10;
}

.amount-custom {
    border: 1px solid {{ $config->color1 ?? '#30acff' }};
    border-radius: 50%;
    padding: 0px 8px;
    color: {{ $config->color1 ?? '#30acff' }};
}


.btn-gray-left {
    background-color: #d3d3d3;
    color: #333;
    border: none;
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
    padding: 0px 14px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn-gray-right {
    background-color: #d3d3d3;
    color: #333;
    border: none;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
    padding: 0px 14px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn-gray-left:hover,
.btn-gray-right:hover {
    background-color: #c0c0c0;
    transform: scale(1.05);
}

.btn-plus {
    background: linear-gradient(135deg, {{ $config->color1 ?? '#82f3fd' }}, {{ $config->color2 ?? '#b2f9ff' }});
    color: #ffffff;
    border-radius: 50%;
    border: 0px solid #333;
    font-size: 20px;
    padding: 0px 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-minus {
    background: linear-gradient(135deg, {{ $config->color1 ?? '#82f3fd' }}, {{ $config->color2 ?? '#b2f9ff' }});
    color: #ffffff;
    border-radius: 50%;
    border: 0px solid #333;
    font-size: 20px;
    padding: 0px 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-plus:hover,
.btn-minus:hover {
    transform: scale(1.1);
}


.custom-height-offcanvas {
    height: 95vh !important;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    overflow-y: auto;
    padding: 0;
}

.custom-height-offcanvas2 {
    height: 70vh !important;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    overflow-y: auto;
    padding: 0;
}

.img-cover-wrapper {
    position: relative;
}

.btn-close-top-left {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: white;
    border-radius: 50%;
    padding: 0.5rem 0.5rem;
    z-index: 10;
}


.count {
    background-color: #e0e0e0;
    padding: 1.5px 0px;
}

.text-alret-blue {
    background-color: #d9fcff;
}

.text-alret-gray {
    background-color: #f3f3f3;
}

.note-count {
    font-weight: bold;
    font-size: 1.25rem;
    min-width: 30px;
    text-align: center;
}

.item-card {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.item-card:hover {
    background-color: #f8f9fa;
}

/* ปุ่มหลักตาม config */
.btn-primary {
    background: linear-gradient(to right, {{ $config->color1 ?? '#007bff' }}, {{ $config->color2 ?? '#0056b3' }}) !important;
    border: none !important;
    color: white !important;
}

.btn-primary:hover {
    background: linear-gradient(to right, {{ $config->color2 ?? '#0056b3' }}, {{ $config->color1 ?? '#007bff' }}) !important;
    transform: translateY(-1px);
}


@media (max-width: 768px) {
    
    
    .page-title {
        font-size: 20px;
    }
    
    .page-title i {
        font-size: 18px;
    }
    
    .search-input {
        font-size: 16px; /* ป้องกัน zoom บนมือถือ */
        padding: 9px 40px 9px 16px;
    }
    
    .category-pill {
        font-size: 12px;
        padding: 5px 14px;
    }
    
    /* Search results adjustments */
    .search-result-image {
        width: 45px;
        height: 45px;
    }
    
    .search-results {
        max-height: 300px;
    }
    
    /* Menu grid adjustments */
    .card-title {
        font-size: 14px;
    }
    
    .title-food {
        font-size: 24px;
    }
}

@media (max-width: 576px) {
    /* Extra small devices */
    .header-section {
        padding: 10px 10px 14px 10px;
        border-radius: 0 0 20px 20px;
    }
    
    .page-title {
        font-size: 18px;
    }
    
    .search-input {
        padding: 8px 35px 8px 14px;
    }
    
    .category-pills {
        gap: 6px;
    }
    
    .category-pill {
        font-size: 11px;
        padding: 4px 12px;
    }
}
    </style>

    <!-- Header Section -->
    <div class="header-section">
        <h1 class="page-title">
            <i class="fas fa-utensils"></i>
            หมวดหมู่ {{ $currentCategory->name ?? 'เมนูอาหาร' }}
        </h1>
        
        <!-- Search Bar -->
        <div class="search-wrapper">
            <input type="text" 
                   class="search-input" 
                   id="searchInput"
                   placeholder="ค้นหาเมนูที่คุณชอบ..." 
                   autocomplete="off">
            <i class="fas fa-search search-icon"></i>
            <button class="clear-search" id="clearSearch">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Category Pills -->
        <div class="category-pills">
            <a href="{{ route('index') }}" class="category-pill">
                <i class="fas fa-home"></i> ทั้งหมด
            </a>
            @foreach($allCategories as $category)
                <a href="{{ route('detail', $category->id) }}" 
                   class="category-pill {{ ($currentCategory && $currentCategory->id == $category->id) ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <!-- Search Results Dropdown -->
        <div class="search-results" id="searchResults">
            <div id="searchResultsContent"></div>
        </div>
    </div>

    <div class="container">
        <div class="d-flex flex-column justify-content-center gap-2">
            
            <!-- แสดงข้อความเมื่อไม่พบผลลัพธ์ -->
            <div id="noResults" class="text-center py-4 d-none">
                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                <h6 class="text-muted">ไม่พบเมนูที่ค้นหา</h6>
                <p class="text-muted small">ลองใช้คำค้นหาอื่น หรือเลือกจากเมนูด้านล่าง</p>
            </div>

            <!--  เมนูทั้งหมด -->
            <div class="row justify-content-center gap-3 menu-grid" id="menuGrid">
                @foreach ($menu as $rs)
                    <div class="col-5 g-0 product-card " style="cursor: pointer; border-radius: 10px;"
                        data-id="{{ $rs['id'] }}">
                        <div class="row g-0 flex-column">
                            <div class="col">
                                <div class="position-relative">
                                    @if ($rs['files'])
                                        <img src="{{ url('storage/' . $rs['files']->file) }}" class="img-fluid rounded"
                                            style="width: 100%; height: 130px; object-fit: cover; border-radius: 10px;"
                                            alt="food">
                                    @else
                                        <img src="{{ asset('foods/default-photo.png') }}" class="img-fluid rounded"
                                            style="width: 100%; height: 130px; object-fit: cover; border-radius: 10px;"
                                            alt="food">
                                    @endif

                                    <!-- แสดงจำนวน  -->
                                    <span class="cart-amount-badge d-none" data-badge-name="{{ $rs['name'] }}">0</span>

                                </div>
                            </div>
                            <div class="col">
                                <div class="p-0 pt-2 text-start" style="background-color: transparent;">
                                    <h5 class="m-0 card-title">{{ $rs['name'] }}</h5>
                                    <p class="fw-bold card-title mb-0">{{ $rs['base_price'] }} ฿</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  สำหรับเพิ่มสินค้าใหม่ -->
                    <div class="offcanvas offcanvas-bottom custom-height-offcanvas border-top-0" tabindex="-1"
                        id="offcanvasAdd-{{ $rs['id'] }}" aria-labelledby="offcanvasAdd">

                        <div class="img-cover-wrapper">
                            <button type="button" class="btn-close btn-close-top-left" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>

                            @if ($rs['files'])
                                <img src="{{ url('storage/' . $rs['files']->file) }}" class="img-cover"
                                    style="width: 100%; height: 200px; object-fit: cover;" alt="food">
                            @else
                                <img src="{{ asset('foods/default-photo.png') }}" class="img-cover"
                                    style="width: 100%; height: 200px; object-fit: cover;" alt="food">
                            @endif
                        </div>

                        <div class="offcanvas-body small px-3">
                            <div class="row justify-content-between align-items-start mb-2">
                                <div class="col-9 text-start ">
                                    <h5 class="offcanvas-title fw-bold mb-0 fs-5  product-name">{{ $rs['name'] }}</h5>
                                    <div class="text-muted ps-1" style="line-height: 1.0;">{{ $rs['detail']}}</div>
                                </div>
                                
                                <div class="col-3 text-start text-end fs-5 fw-bold" style="line-height: 1.0;">
                                    {{ $rs['base_price'] }} ฿<br>
                                    <span class="text-muted"
                                        style="font-size: 14px; font-weight: normal;">ราคาเริ่มต้น</span>
                                </div>
                            </div>
                            <hr class="my-1">
                            <input type="hidden" id="uuid" value="">
                            <input type="hidden" id="base_pricex" class="base_pricex" value="{{ $rs['base_price'] }}">
                            @foreach ($rs['option'] as $type => $optionGroup)
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <h6 class="fs-6 fw-bold mb-0">{{ $type }}</h6>
                                    <small
                                        class="text-muted px-2  rounded-5 {{ $optionGroup['is_selected'] == 1 ? 'text-alret-blue' : 'text-alret-gray' }}">
                                        @if ($optionGroup['is_selected'] == 1)
                                            เลือก {{ $optionGroup['amout'] }}
                                        @else
                                            ไม่จำเป็นต้องระบุ
                                        @endif
                                    </small>
                                </div>

                                @foreach ($optionGroup['items'] as $option)
                                    <div class="d-flex justify-content-between align-items-center py-0">
                                        <div
                                            class="form-check d-flex justify-content-between align-items-center w-100 mb-0 py-0">
                                            <div>
                                                <input class="form-check-input me-2 option-checkbox" type="checkbox"
                                                    data-limit="{{ $optionGroup['amout'] }}"
                                                    data-required="{{ $optionGroup['is_selected'] }}"
                                                    data-group="group_{{ $rs['id'] }}_{{ $type }}"
                                                    data-rs-id="{{ $rs['id'] }}"
                                                    data-categoryId="{{ $rs['category_id'] }}"
                                                    data-type="{{ $type }}" data-price="{{ $option->price }}"
                                                    data-label="{{ $option->name }}" id="option_{{ $option->id }}"
                                                    @if ($loop->first) data-base-price="{{ $rs['base_price'] }}" @endif
                                                    name="option_{{ $rs['id'] }}_{{ Str::slug($type, '_') }}[]"
                                                    value="{{ $option->id }}">
                                                <label class="form-check-label" for="option_{{ $option->id }}">
                                                    {{ $option->name }}
                                                </label>
                                            </div>
                                            <div class="d-flex justify-content-end align-items-center"
                                                style="min-width: 60px;">
                                                <i class="fa-solid fa-plus" style="font-size: 9px; margin-right: 1px;"></i>
                                                <span>{{ $option->price }}</span>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                                <hr>
                            @endforeach
                            <div class="mt-3 text-start ">
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <label for="note_{{ $rs['id'] }}"
                                        class="form-label fw-bold fs-6">หมายเหตุถึงร้านอาหาร</label>
                                    <small class="text-muted px-2  rounded-5 text-alret-gray">
                                        ไม่จำเป็นต้องระบุ
                                    </small>
                                </div>
                                <textarea id="note_{{ $rs['id'] }}" name="note_{{ $rs['id'] }}" class="form-control" rows="3"
                                    placeholder="ระบุรายละเอียดคำขอ (ขึ้นอยู่กับดุลยพินิจของร้าน)"></textarea>
                            </div>
                            <div class="d-flex justify-content-center align-items-center gap-2 mt-3 "
                                style="margin-bottom: 4rem;">
                                <button type="button" class="btn-minus" data-id="{{ $rs['id'] }}"><i
                                        class="fa-solid fa-minus"></i></button>
                                <div class="note-count fw-bold fs-5" data-id="{{ $rs['id'] }}"
                                    style="min-width: 30px; text-align: center;">1</div>
                                <button type="button" class="btn-plus" data-id="{{ $rs['id'] }}"><i
                                        class="fa-solid fa-plus"></i></button>
                            </div>
                            <div class="fixed-bottom py-3"
                                style="background-color: white; z-index: 999; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); border: none;">
                                <div class="container text-center">
                                    <button id="add-to-cart-btn" class=" add-to-cart-btn btn btn-primary w-100 "
                                        style="font-size: 16px; border-radius: 25px;" data-rs-id="{{ $rs['id'] }}"
                                        data-category-id="{{ $rs['category_id'] }}" disabled>
                                        เพิ่มไปยังตะกร้า - <span id="total-price" class="total-pricex" data-id="{{ $rs['id'] }}">{{ $rs['base_price'] }}</span> ฿
                                    </button>
                                    <button id="back-menu" class=" back-menu btn btn-primary w-100 "
                                        style="font-size: 16px; border-radius: 25px;" data-rs-id="{{ $rs['id'] }}"
                                        hidden>
                                        กลับไปยังเมนู
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 🔻 Offcanvas สำหรับสินค้าเดิมในตะกร้า -->
                    <div class="offcanvas offcanvas-bottom custom-height-offcanvas2 border-top-0" tabindex="-1"
                        id="offcanvasEdit-{{ $rs['id'] }}" aria-labelledby="offcanvasBottomLabel">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h5 class="offcanvas-title fw-bold mb-0 fs-5  product-name">{{ $rs['name'] }}</h5>
                            <span class="text-end fs-5 fw-bold" style="line-height: 1.0;">
                                {{ $rs['base_price'] }} ฿<br>
                                <span class="text-muted" style="font-size: 14px; font-weight: normal;">ราคาเริ่มต้น</span>
                            </span>
                        </div>
                        <hr class="my-2">
                        <div class="offcanvas-body pt-1 px-3">
                            <!-- JavaScript จะเติมข้อมูลสินค้า id ตรงกันไว้ตรงนี้ -->
                        </div>
                        <div class="fixed-bottom py-3"
                            style="background-color: white; z-index: 999; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); border: none;">
                            <div class="container text-center">
                                <button id="addList" class=" btn btn-primary w-100 add-button"
                                    style="font-size: 16px; border-radius: 25px;" data-rs-id="{{ $rs['id'] }}" data-rs-price="{{$rs['base_price']}}">
                                    สั่งรายการนี้เพิ่ม
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ✅ JavaScript สำหรับค้นหา
        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');
        const searchResults = document.getElementById('searchResults');
        const searchResultsContent = document.getElementById('searchResultsContent');
        const noResults = document.getElementById('noResults');
        const menuGrid = document.getElementById('menuGrid');
        
        let searchTimeout;
        let menuData = [];
        
        @if(isset($menu))
        menuData = @json($menu);
        @endif
        
        function searchMenus(query) {
            if (!query.trim()) {
                hideSearchResults();
                return;
            }
            
            const results = menuData.filter(item => 
                item.name.toLowerCase().includes(query.toLowerCase()) ||
                (item.detail && item.detail.toLowerCase().includes(query.toLowerCase()))
            );
            
            if (results.length > 0) {
                displaySearchResults(results, query);
            } else {
                showNoResults();
            }
        }
        
        function displaySearchResults(results, query) {
            searchResultsContent.innerHTML = '';
            
            results.forEach(item => {
                const highlightedName = highlightText(item.name, query);
                const highlightedDetail = item.detail ? highlightText(item.detail, query) : '';
                
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item p-3 mb-2';
                resultItem.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-2">
                            <img src="${item.files ? '{{ url("storage") }}/' + item.files.file : '{{ asset("foods/default-photo.png") }}'}" 
                                 alt="${item.name}" 
                                 class="search-result-image">
                        </div>
                        <div class="col-7">
                            <h6 class="mb-1">${highlightedName}</h6>
                            ${highlightedDetail ? `<p class="text-muted small mb-0">${highlightedDetail}</p>` : ''}
                        </div>
                        <div class="col-3 text-end">
                            <span class="fw-bold">${item.base_price} ฿</span>
                        </div>
                    </div>
                `;
                
                resultItem.addEventListener('click', function() {
                    const menuCard = document.querySelector(`[data-id="${item.id}"]`);
                    if (menuCard) {
                        hideSearchResults();
                        searchInput.value = '';
                        clearSearchBtn.classList.add('d-none');
                        
                        menuCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        setTimeout(() => {
                            menuCard.click();
                        }, 500);
                    }
                });
                
                searchResultsContent.appendChild(resultItem);
            });
            
            searchResults.classList.add('show');
            noResults.classList.add('d-none');
            menuGrid.classList.add('searching');
        }
        
        function showNoResults() {
            searchResultsContent.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <div>ไม่พบเมนูที่ค้นหา</div>
                </div>
            `;
            searchResults.classList.add('show');
            menuGrid.classList.add('searching');
        }
        
        function hideSearchResults() {
            searchResults.classList.remove('show');
            noResults.classList.add('d-none');
            menuGrid.classList.remove('searching');
        }
        
        function highlightText(text, query) {
            if (!query.trim()) return text;
            
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\                resultItem.addEventListener('click', function() {
                    const menuCard = document.querySelector(`[data-id="${item.id}"]`')})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query) {
                clearSearchBtn.classList.add('show');
            } else {
                clearSearchBtn.classList.remove('show');
                hideSearchResults();
            }
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchMenus(query);
            }, 300);
        });
        
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.remove('show');
            hideSearchResults();
            searchInput.focus();
        });
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.header-section')) {
                hideSearchResults();
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstResult = searchResultsContent.querySelector('.search-result-item');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });

        // ✅ ส่วนที่เหลือของ JavaScript เดิม (ไม่เปลี่ยนแปลง)
        function closeAllOffcanvas() {
            const openOffcanvas = document.querySelector('.offcanvas.show');
            if (openOffcanvas) {
                const instance = bootstrap.Offcanvas.getInstance(openOffcanvas);
                if (instance) instance.hide();
            }
        }

        const addButtons = document.querySelectorAll('.add-button');

        addButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.rsId;
                const basePrice = this.dataset.rsPrice;
                const targetId = `#offcanvasAdd-${productId}`;
                const offcanvasEl = document.querySelector(targetId);
                if (!offcanvasEl) return;
                const uniqId = document.getElementById('uuid');
                if (uniqId && uniqId.value) {
                    uniqId.value = '';
                }

                const checkboxes = offcanvasEl.querySelectorAll('input.option-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.removeAttribute('disabled');
                });

                const noteField = offcanvasEl.querySelector(`#note_${productId}`);
                if (noteField) noteField.value = '';

                const noteCountDiv = offcanvasEl.querySelector(
                    `.note-count[data-id="${productId}"]`);
                if (noteCountDiv) {
                    noteCountDiv.textContent = '1';
                }

                let currentCount = parseInt(noteCountDiv?.textContent) || 0;

                if (currentCount < 0) currentCount = 0;
                noteCountDiv.textContent = currentCount;

                const addToCartBtn = document.querySelector(
                    `.add-to-cart-btn[data-rs-id="${productId}"]`);
                const backMenuBtn = document.querySelector(
                    `.back-menu[data-rs-id="${productId}"]`);   
                const base_price = offcanvasEl.querySelector('.base_pricex');
                const price = offcanvasEl.querySelector('.total-pricex');
                    if (currentCount === 0) {
                        addToCartBtn?.setAttribute('hidden', true);
                        backMenuBtn?.removeAttribute('hidden');
                    } else {
                        addToCartBtn?.removeAttribute('hidden');
                        if (checkboxes.length > 0) {
                            addToCartBtn?.setAttribute('disabled', '');
                        } else {
                            addToCartBtn?.removeAttribute('disabled');
                        }
                        price.textContent = base_price.value;
                        backMenuBtn?.setAttribute('hidden', true);
                    }

                closeAllOffcanvas();

                setTimeout(() => {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(
                        offcanvasEl);
                    bsOffcanvas.show();
                }, 100);
            });
        });

        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.id;
                
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const matchingItems = cart.filter(item => item.id === productId);

                const targetId = matchingItems.length > 0 ?
                    `#offcanvasEdit-${productId}` :
                    `#offcanvasAdd-${productId}`;
                const targetEl = document.querySelector(targetId);
                if (!targetEl) return;

                if (matchingItems.length > 0) {
                    const container = targetEl.querySelector('.offcanvas-body');
                    container.innerHTML = '';

                    matchingItems.forEach(item => {
                        const optionsText = item.options.map(opt => `${opt.label}`)
                            .join(', ');
                        const html = `
                        <div class="card mb-2 item-card border-0 border-bottom rounded-0" data-uuid="${item.uuid}" data-product-id="${item.id}">
                            <div class="card-body text-start p-2 cursor-pointer">
                                <div class="row justify-content-between align-items-start fs-6">
                                    <div class="col-9 d-flex flex-column justify-content-start lh-sm">
                                        <div class="card-title m-0">${item.name}</div>
                                        <div class="text-muted" style="font-size: 12px;">${optionsText || '-'}</div>
                                    </div>
                                    <div class="col-1 mt-1 amount-custom text-center">${item.amount}</div>
                                    <div class="col-2 text-end">${item.total_price}</div>
                                </div>
                            </div>
                        </div>
                    `;
                        container.innerHTML += html;
                    });

                    setTimeout(() => {
                        const itemCards = container.querySelectorAll('.item-card');
                        itemCards.forEach(innerCard => {
                            innerCard.addEventListener('click', function() {
                                const uuid = this.dataset.uuid;
                                const productId = this.dataset.productId;
                                const addOffcanvas = document.querySelector(`#offcanvasAdd-${productId}`);
                                const item = cart.find(i => i.uuid === uuid);

                                if (!item || !addOffcanvas) return;

                                document.getElementById('uuid').value = uuid;

                                const noteField = addOffcanvas.querySelector(`#note_${productId}`);
                                const amountEl = addOffcanvas.querySelector(`.note-count[data-id="${productId}"]`);
                                const totalPriceEl = addOffcanvas.querySelector(`#total-price`);
                                const checkboxes = addOffcanvas.querySelectorAll('input.option-checkbox');

                                checkboxes.forEach(checkbox => {
                                    checkbox.checked = item.options?.some(opt => opt.id === checkbox.value) || false;
                                });

                                if (noteField) noteField.value = item.note || '';
                                if (amountEl) amountEl.textContent = item.amount || 1;
                                if (totalPriceEl) totalPriceEl.textContent = item.total_price || item.base_price;

                                const currentCount = parseInt(amountEl?.textContent) || 0;
                                const addToCartBtn = document.querySelector(`.add-to-cart-btn[data-rs-id="${productId}"]`);
                                const backMenuBtn = document.querySelector(`.back-menu[data-rs-id="${productId}"]`);

                                if (currentCount === 0) {
                                    addToCartBtn?.setAttribute('hidden', true);
                                    backMenuBtn?.removeAttribute('hidden');
                                } else {
                                    addToCartBtn?.removeAttribute('hidden');
                                    if (checkboxes.length > 0) {
                                        addToCartBtn?.removeAttribute('disabled');
                                    } else {
                                        addToCartBtn?.removeAttribute('disabled');
                                    }
                                    backMenuBtn?.setAttribute('hidden', true);
                                }
                                closeAllOffcanvas();

                                setTimeout(() => {
                                    const bsAdd = bootstrap.Offcanvas.getOrCreateInstance(addOffcanvas);
                                    bsAdd.show();
                                }, 300);
                            });
                        });
                    }, 100);

                } else {
                    const offcanvasEl = document.querySelector(targetId);
                    if (!offcanvasEl) return;

                    const uniqId = document.getElementById('uuid');
                    if (uniqId) uniqId.value = '';

                    const noteField = targetEl.querySelector(`#note_${productId}`);
                    if (noteField) noteField.value = '';

                    const checkboxes = targetEl.querySelectorAll('input.option-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = false);

                    const amountDisplay = targetEl.querySelector(`.note-count[data-id="${productId}"]`);
                    if (amountDisplay) amountDisplay.textContent = '1';

                    const totalPriceEl = targetEl.querySelector(`#total-price`);
                    const basePrice = totalPriceEl?.dataset?.basePrice;
                    if (totalPriceEl && basePrice) totalPriceEl.textContent = basePrice;

                    const noteCountDiv = document.querySelector(`.note-count[data-id="${productId}"]`);
                    const addToCartBtn = document.querySelector(`.add-to-cart-btn[data-rs-id="${productId}"]`);
                    const backMenuBtn = document.querySelector(`.back-menu[data-rs-id="${productId}"]`);
                    let currentCount = parseInt(noteCountDiv.textContent) || 0;

                    if (currentCount < 0) currentCount = 0;
                    noteCountDiv.textContent = currentCount;
                    const base_price = offcanvasEl.querySelector('.base_pricex');
                    const price = offcanvasEl.querySelector('.total-pricex');
                    if (currentCount === 0) {
                        addToCartBtn?.setAttribute('hidden', true);
                        backMenuBtn?.removeAttribute('hidden');
                    } else {
                        addToCartBtn?.removeAttribute('hidden');
                        if (checkboxes.length > 0) {
                            addToCartBtn?.setAttribute('disabled', '');
                        } else {
                            addToCartBtn?.removeAttribute('disabled');
                        }
                        price.textContent = base_price.value;
                        backMenuBtn?.setAttribute('hidden', true);
                    }
                }
                closeAllOffcanvas();
                setTimeout(() => {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(targetEl);
                    bsOffcanvas.show();
                }, 100);
            });
        });

        function updateAmountBadgesFromCart() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];

            document.querySelectorAll('[data-badge-name]').forEach(badge => {
                const name = badge.dataset.badgeName;
                const totalAmount = cart
                    .filter(item => item.name === name)
                    .reduce((sum, item) => sum + (item.amount || 0), 0);

                if (totalAmount > 0) {
                    badge.textContent = totalAmount;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            });
        }

        updateAmountBadgesFromCart();

        const originalSetItem = localStorage.setItem;
        localStorage.setItem = function(key, value) {
            originalSetItem.apply(this, arguments);
            if (key === 'cart') updateAmountBadgesFromCart();
        };

        const checkboxes = document.querySelectorAll('.option-checkbox');

        function handleCheckboxChange(event) {
            const cb = event.target;
            const rsId = cb.dataset.rsId;
            const group = cb.dataset.group;
            const limit = parseInt(cb.dataset.limit);
            const required = parseInt(cb.dataset.required);

            const groupCheckboxes = Array.from(document.querySelectorAll(
                `.option-checkbox[data-rs-id="${rsId}"][data-group="${group}"]`
            ));
            const checked = groupCheckboxes.filter(cb => cb.checked);

            if (required === 1) {
                groupCheckboxes.forEach(item => {
                    item.disabled = !item.checked && checked.length >= limit;
                });
            } else {
                groupCheckboxes.forEach(item => {
                    item.disabled = false;
                });
            }

            const basePriceElement = document.querySelector(`.option-checkbox[data-rs-id="${rsId}"]`);
            let totalPrice = parseFloat(basePriceElement?.dataset.basePrice || 0);

            const rsCheckboxes = Array.from(document.querySelectorAll(
                `.option-checkbox[data-rs-id="${rsId}"]`));
            rsCheckboxes.forEach(cb => {
                if (cb.checked) {
                    totalPrice += parseFloat(cb.dataset.price) || 0;
                }
            });

            const priceLabel = document.querySelector(`#offcanvasAdd-${rsId} #total-price`);
            if (priceLabel) {
                priceLabel.textContent = totalPrice.toFixed(2);
            }

            const groups = [...new Set(rsCheckboxes.map(cb => cb.dataset.group))];
            let allRequiredGroupsValid = true;

            groups.forEach(groupKey => {
                const groupCBs = rsCheckboxes.filter(cb => cb.dataset.group === groupKey);
                const groupRequired = parseInt(groupCBs[0]?.dataset.required || 0);
                const groupLimit = parseInt(groupCBs[0]?.dataset.limit || 0);
                const checkedCount = groupCBs.filter(cb => cb.checked).length;

                if (groupRequired === 1 && checkedCount !== groupLimit) {
                    allRequiredGroupsValid = false;
                }

                updateTotalPrice(rsId);
            });

            const addToCartBtn = document.querySelector(`#offcanvasAdd-${rsId} #add-to-cart-btn`);
            if (addToCartBtn) {
                addToCartBtn.disabled = !allRequiredGroupsValid;
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', handleCheckboxChange));

        function changeNoteQty(rsId, delta) {
            const noteCountDiv = document.querySelector(`.note-count[data-id="${rsId}"]`);
            const addToCartBtn = document.querySelector(`.add-to-cart-btn[data-rs-id="${rsId}"]`);
            const backMenuBtn = document.querySelector(`.back-menu[data-rs-id="${rsId}"]`);
            
            let currentCount = parseInt(noteCountDiv.textContent) || 0;
            if(delta){
                currentCount += delta;
            }
            
            if (currentCount < 0) currentCount = 0;
            noteCountDiv.textContent = currentCount;

            if (currentCount === 0) {
                addToCartBtn?.setAttribute('hidden', true);
                backMenuBtn?.removeAttribute('hidden');
            } else {
                addToCartBtn?.removeAttribute('hidden');
                backMenuBtn?.setAttribute('hidden', true);
            }

            updateTotalPrice(rsId);
        }

        function updateTotalPrice(rsId) {
            const rsCheckboxes = Array.from(document.querySelectorAll(
                `.option-checkbox[data-rs-id="${rsId}"]`
            ));

            const basePriceElement = rsCheckboxes[0];
            let totalPerItem = parseFloat(basePriceElement?.dataset.basePrice || 0);

            rsCheckboxes.forEach(cb => {
                if (cb.checked) {
                    totalPerItem += parseFloat(cb.dataset.price) || 0;
                }
            });

            const noteCountDiv = document.querySelector(`.note-count[data-id="${rsId}"]`);
            const qty = parseInt(noteCountDiv?.textContent) || 0;

            const finalTotal = totalPerItem * qty;

            const priceLabel = document.querySelector(`#offcanvasAdd-${rsId} #total-price`);
            if (priceLabel) {
                priceLabel.textContent = finalTotal.toFixed(2);
            }

            const totalPriceEl = document.querySelector(`.total-pricex[data-id="${rsId}"]`);
            if (totalPriceEl) {
                totalPriceEl.textContent = finalTotal.toFixed(2);
            }
        }

        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const rsId = this.dataset.id;
                changeNoteQty(rsId, -1);
            });
        });

        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const rsId = this.dataset.id;
                changeNoteQty(rsId, 1);
            });
        });

        document.querySelectorAll('.back-menu').forEach(btn => {
            btn.addEventListener('click', function() {
                const rsId = this.dataset.rsId;
                const uuid = document.getElementById('uuid')?.value;

                if (uuid) {
                    let cart = JSON.parse(localStorage.getItem('cart')) || [];
                    const updatedCart = cart.filter(item => item.uuid !== uuid);
                    localStorage.setItem('cart', JSON.stringify(updatedCart));
                    updateAmountBadgesFromCart();
                }

                const offcanvasEl = this.closest('.offcanvas');
                const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                bsOffcanvas?.hide();
            });
        });

        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

        addToCartButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const rsId = this.dataset.rsId;
                const crId = this.dataset.categoryId;
                
                const offcanvasEl = this.closest('.offcanvas');

                const basePriceEl = offcanvasEl.querySelector('.base_pricex');
                const basePrice = parseFloat(basePriceEl.value);
                const noteTextarea = document.getElementById(`note_${rsId}`);
                const noteText = noteTextarea ? noteTextarea.value : '';

                const noteCountDiv = document.querySelector(`.note-count[data-id="${rsId}"]`);
                const amount = noteCountDiv ? parseInt(noteCountDiv.textContent) || 0 : 0;

                const allCheckboxes = offcanvasEl.querySelectorAll('.option-checkbox');
                const selectedOptions = [];
                let totalOptionPrice = 0;

                allCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        const option = {
                            id: cb.value || cb.dataset.optionId || null,
                            label: cb.dataset.label || 'ไม่ทราบชื่อ',
                            type: cb.dataset.type || 'ไม่ทราบชื่อ',
                            price: parseFloat(cb.dataset.price || 0),
                        };
                        totalOptionPrice += option.price;
                        selectedOptions.push(option);
                    }
                });

                const totalPrice = basePrice + totalOptionPrice;

                const uniqId = document.getElementById('uuid')?.value || '';
                const newUuid = uniqId || crypto.randomUUID();

                const product = {
                    uuid: newUuid,
                    id: rsId,
                    category_id: crId,
                    name: offcanvasEl.querySelector('.product-name')?.textContent || 'ไม่ทราบชื่อเมนู',
                    base_price: basePrice,
                    total_price: (totalPrice || 0) * (amount || 1),
                    options: selectedOptions,
                    note: noteText,
                    amount: amount,
                };

                let cart = JSON.parse(localStorage.getItem('cart')) || [];

                if (uniqId) {
                    const index = cart.findIndex(item => item.uuid === uniqId);
                    if (index !== -1) {
                        cart[index] = product;
                    } else {
                        cart.push(product);
                    }
                } else {
                    cart.push(product);
                }

                localStorage.setItem('cart', JSON.stringify(cart));

                const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (bsOffcanvas) {
                    bsOffcanvas.hide();
                }
            });
        });

        const hash = window.location.hash;
        if (hash && hash.startsWith('#select-')) {
            const [idPart, uuidPart] = hash.replace('#select-', '').split('&uuid=');
            const itemId = idPart;
            const itemUuid = uuidPart;

            const card = document.querySelector(`[data-id="${itemId}"]`);
            if (card) {
                card.click();

                setTimeout(() => {
                    const listItem = document.querySelector(`[data-uuid="${itemUuid}"]`);
                    if (listItem) {
                        listItem.click();
                    }
                }, 500);
            }
        }
    });
    </script>
@endsection