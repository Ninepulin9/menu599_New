<!DOCTYPE html>
<html lang="th" class="light-style layout-menu-fixed" dir="ltr"data-theme="theme-default" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>ระบบร้านค้า</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-channel" content="{{ request()->header('channel', '') }}">
    <meta name="app-device" content="{{ request()->header('device', '') }}">
    <link rel="icon" type="image/x-icon" href="{{asset('assets/img/favicon/favicon.ico')}}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('assets/vendor/fonts/boxicons.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/css/demo.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
    <script src="{{asset('assets/vendor/js/helpers.js')}}"></script>
    <script src="{{asset('assets/js/config.js')}}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Noto+Sans+Thai:wght@100..900&family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    <script>
  const PUSHER_APP_KEY = "{{ env('PUSHER_APP_KEY') }}";
  const PUSHER_APP_CLUSTER = "{{ env('PUSHER_APP_CLUSTER') }}";

  Pusher.logToConsole = true;
  var pusher = new Pusher(PUSHER_APP_KEY, { cluster: PUSHER_APP_CLUSTER, encrypted: true });
  var channel = pusher.subscribe('orders');

  function playNotify() {
    const el = document.getElementById('notifySound');
    if (!el) return;

    try {
      el.currentTime = 0;
      const p = el.play();
      if (p && typeof p.then === 'function') {
        p.catch((err) => {
          console.warn('Autoplay blocked:', err);
          const once = () => {
            el.currentTime = 0;
            el.play().catch(() => {});
          };
          window.addEventListener('click', once, { once: true, passive: true });
          window.addEventListener('touchstart', once, { once: true, passive: true });
        });
      }
    } catch (e) {
      console.error('play() error:', e);
      throw e;
    }
  }

  // ปลดล็อกเสียงครั้งแรกเมื่อมี gesture (กัน NotAllowedError บน iOS/Chrome)
  function unlockAudioOnce() {
    const el = document.getElementById('notifySound');
    if (!el) return;
    const handler = () => {
      try { el.play().then(() => { el.pause(); el.currentTime = 0; }).catch(()=>{}); } catch(_){}
      window.removeEventListener('click', handler);
      window.removeEventListener('touchstart', handler);
    };
    window.addEventListener('click', handler, { once: true, passive: true });
    window.addEventListener('touchstart', handler, { once: true, passive: true });
  }
  document.addEventListener('DOMContentLoaded', unlockAudioOnce);

  function showOrderNotification(order) {
    if (!order) return;
    const container = document.getElementById('orderNotifications');
    if (!container) return;
    const box = document.createElement('div');
    box.className = 'order-alert';
    const title = order.table_number ? `โต๊ะ ${order.table_number}` : 'ออเดอร์ออนไลน์';
    const items = (order.items || []).join(', ');
    box.innerHTML = `<strong>${title}</strong><br>${items}<br><small>${order.created_at}</small><span class="close">&times;</span>`;
    box.querySelector('.close').addEventListener('click', (e) => {
      e.stopPropagation();
      box.remove();
    });
    box.addEventListener('click', () => {
      const url = order.is_online ? `/admin/order_rider?highlight=${order.id}` : `/admin/order?highlight=${order.table_number}`;
      window.location.href = url;
    });
    container.appendChild(box);
  }

  channel.bind('App\\Events\\OrderCreated', function(data) {
    console.log(data.order[0]);
    

    
    // เรียกการเช็คออเดอร์ใหม่ก่อน เพื่อให้ระบบออโต้ปริ้นทำงานทันที
    if (typeof checkNewOrders === 'function') {
      checkNewOrders();
    }
    
    // แสดง Popup หลังการพิมพ์ (Android ไม่มีเสียงแจ้งเตือน)
    setTimeout(() => {
      const meta = document.querySelector('meta[name="app-device"]');
      const isAndroid = meta && meta.getAttribute('content').toLowerCase() === 'android';
      if (!isAndroid) {
        try {
          playNotify();
        } catch (e) {
          console.error('notify sound error:', e);
        }
      }
      Swal.fire({
        icon: 'info',
        title: data.order[0],
        timer: 1000,
        showConfirmButton: false
      });
    }, 1000);
  });
</script>

    <style>
        body {
            font-family: "Noto Sans Thai", sans-serif;
            font-optical-sizing: auto;
        }
        #orderNotifications {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 2000;
        }

        .order-alert {
            position: relative;
            background: #fff;
            border-left: 4px solid #0d6efd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            padding: 0.5rem 1.5rem 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            min-width: 260px;
        }

        .order-alert .close {
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 1.2rem;
            line-height: 1;
            cursor: pointer;
        }

        .highlight-row {
            background: #fff3cd !important;
        }
    </style>
    @yield('style')
</head>

<body>
    <audio id="notifySound" src="{{asset('sound/test.mp3')}}" preload="auto" playsinline></audio>
    <div id="orderNotifications"></div>
    @if ($message = Session::get('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '{{ $message }}',
        })
    </script>
    @endif
    @if($message = Session::get('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: '{{ $message }}',
        })
    </script>
    @endif
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('admin.menu')
            <div class="layout-page">
                @include('admin.navheader')
                @yield('content')
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                        <div class="mb-2 mb-md-0">
                            ©
                            <script>
                                document.write(new Date().getFullYear());
                            </script>
                            , So Fin By So Smart Solution
                        </div>
                    </div>
                </footer>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <script src="{{asset('assets/vendor/libs/jquery/jquery.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/popper/popper.js')}}"></script>
    <script src="{{asset('assets/vendor/js/bootstrap.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
    <script src="{{asset('assets/vendor/js/menu.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
    <script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script>
        function checkNewOrders() {
            fetch("{{ route('checkNewOrders') }}")
                .then(response => response.json())
                .then(res => {
                    if (res.status) {
                        if (res.order) {
                            showOrderNotification(res.order);
                        }
                        const channelMeta = document.querySelector('meta[name="app-channel"]');
                        const deviceMeta = document.querySelector('meta[name="app-device"]');
                        const channel = channelMeta ? channelMeta.content : '';
                        const device = deviceMeta ? deviceMeta.content : '';

                        const targetId = res.table_id ?? res.order_id;
                        if (targetId) {
                            const printUrl = `/admin/order/printOrderAdminCook/${targetId}?channel=${channel}&device=${device}`;
                            try {
                                sessionStorage.setItem('admin-prev-url', window.location.href);
                            } catch (e) {
                                console.warn('sessionStorage unavailable', e);
                            }
                            window.location.href = printUrl;
                        }
                    }
                })
                .catch(err => console.error(err));
        }

        setInterval(checkNewOrders, 1000);

        window.addEventListener('message', function(e) {
            if (e.data === 'cook-print-done') {
                Swal.fire({
                    icon: 'success',
                    title: 'ปริ้น Order ในครัวแบบออโต้เรียบร้อยแล้ว',
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        });
    </script>
    
</body>

</html>
@yield('script')