<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\admin\Category;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\LogStock;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\MenuStock;
use App\Models\MenuTypeOption;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\OrdersOption;
use App\Models\Promotion;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Config;
use App\Models\ConfigPromptpay;
use PromptPayQR\Builder as PromptPayQRBuilder;
use App\Models\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\DB; 

class Main extends Controller
{
    public function index(Request $request)
    {
        $table_id = $request->input('table');
        if ($table_id) {
            session(['table_id' => $table_id]);
        }
        // Test
        // ดึงโปรโมชั่นที่เปิดใช้งาน
        $promotion = Promotion::where('is_status', 1)->get();
        
        // ดึงหมวดหมู่ที่มีเมนูพร้อมขายในเวลาปัจจุบันเท่านั้น
        $category = Categories::whereHas('menu', function($query) {
            $query->availableNow(); // ใช้ scope ที่สร้างไว้ใน Menu Model
        })->with('files')->get();
        
        return view('users.main_page', compact('category', 'promotion'));
    }

    public function detail($id)
    {
        $item = [];
        
        // ดึงเมนูที่สามารถสั่งได้ในเวลาปัจจุบันเท่านั้น
        $menu = Menu::where('categories_id', $id)
                   ->availableNow() // ใช้ scope
                   ->with('files')
                   ->orderBy('created_at', 'asc')
                   ->get();
        
        foreach ($menu as $key => $rs) {
            // ตรวจสอบสถานะการขายอีกครั้ง
            if (!$rs->isAvailable()) {
                continue; // ข้ามเมนูที่ไม่สามารถสั่งได้
            }
            
            $item[$key] = [
                'id' => $rs->id,
                'category_id' => $rs->categories_id,
                'name' => $rs->name,
                'detail' => $rs->detail,
                'base_price' => $rs->base_price,
                'files' => $rs['files'],
                'is_available' => $rs->isAvailable(),
                'availability_message' => $rs->getAvailabilityMessage(),
                'stock_quantity' => $rs->stock_quantity,
                'is_out_of_stock' => $rs->is_out_of_stock
            ];
            
            $typeOption = MenuTypeOption::where('menu_id', $rs->id)->get();
            if (count($typeOption) > 0) {
                foreach ($typeOption as $typeOptions) {
                    $optionItem = [];
                    $option = MenuOption::where('menu_type_option_id', $typeOptions->id)->get();
                    foreach ($option as $options) {
                        $optionItem[] = (object)[
                            'id' => $options->id,
                            'name' => $options->type,
                            'price' => $options->price
                        ];
                    }
                    $item[$key]['option'][$typeOptions->name] = [
                        'is_selected' => $typeOptions->is_selected,
                        'amout' => $typeOptions->amout,
                        'items' =>  $optionItem
                    ];
                }
            } else {
                $item[$key]['option'] = [];
            }
        }
        $menu = $item;
        return view('users.detail_page', compact('menu'));
    }

    public function order()
    {
        return view('users.list_page');
    }

    public function SendOrder(Request $request)
{
    // เพิ่ม Debug
    \Log::info('SendOrder Debug:', [
        'request_data' => $request->all(),
        'cart' => $request->input('cart')
    ]);
    
    $data = [
        'status' => false,
        'message' => 'สั่งออเดอร์ไม่สำเร็จ',
    ];
    
    $orderData = $request->input('cart');
    $remark = $request->input('remark', ''); // remark ทั่วไป (ถ้ามี)
    $item = array();
    $total = 0;
    $allRemarks = []; // เก็บ remark จากทุกเมนู
    
    // ตรวจสอบเมนูก่อนทำการสั่ง
    foreach ($orderData as $key => $order) {
        $menu = Menu::find($order['id']);
        
        // ตรวจสอบว่าเมนูยังสามารถสั่งได้หรือไม่
        if (!$menu || !$menu->isAvailable()) {
            $menuName = $menu ? $menu->name : 'ไม่พบเมนู';
            $message = $menu ? $menu->getAvailabilityMessage() : 'ไม่พบเมนู';
            $data['message'] = "เมนู '{$menuName}' ไม่สามารถสั่งได้ในขณะนี้: {$message}";
            return response()->json($data);
        }
        
        // ตรวจสอบสต็อก
        if (!$menu->hasStock($order['amount'])) {
            $data['message'] = "เมนู '{$menu->name}' มีจำนวนไม่เพียงพอ";
            return response()->json($data);
        }
        
        // รวบรวม remark จากแต่ละเมนู
        if (!empty($order['note'])) {
            $allRemarks[] = $menu->name . ': ' . $order['note'];
        }
        
        $item[$key] = [
            'menu_id' => $order['id'],
            'quantity' => $order['amount'],
            'price' => $order['total_price']
        ];
        
        if (!empty($order['options'])) {
            foreach ($order['options'] as $rs) {
                $item[$key]['option'][] = $rs['id'];
            }
        } else {
            $item[$key]['option'] = [];
        }
        $total = $total + $order['total_price'];
    }
    
    // รวม remark ทั้งหมด
    $combinedRemark = '';
    
    // เพิ่ม remark ทั่วไป (ถ้ามี)
    if (!empty($remark)) {
        $combinedRemark .= $remark;
    }
    
    // เพิ่ม remark จากแต่ละเมนู
    if (!empty($allRemarks)) {
        if (!empty($combinedRemark)) {
            $combinedRemark .= ' | ';
        }
        $combinedRemark .= implode(' | ', $allRemarks);
    }
    
    \Log::info('Combined Remarks:', [
        'general_remark' => $remark,
        'menu_remarks' => $allRemarks,
        'combined_remark' => $combinedRemark
    ]);
    
    if (!empty($item)) {
        $order = new Orders();
        $order->table_id = session('table_id') ?? '1';
        $order->total = $total;
        $order->remark = $combinedRemark; 
        $order->status = 1;
        
        if ($order->save()) {
            foreach ($item as $rs) {
                $orderdetail = new OrdersDetails();
                $orderdetail->order_id = $order->id;
                $orderdetail->menu_id = $rs['menu_id'];
                $orderdetail->quantity = $rs['quantity'];
                $orderdetail->price = $rs['price'];
                
                if ($orderdetail->save()) {
                    // ลดสต็อกเมนู
                    $menu = Menu::find($rs['menu_id']);
                    if ($menu) {
                        $menu->decreaseStock($rs['quantity']);
                    }
                    
                    foreach ($rs['option'] as $key => $option) {
                        $orderOption = new OrdersOption();
                        $orderOption->order_detail_id = $orderdetail->id;
                        $orderOption->option_id = $option;
                        $orderOption->save();
                        
                        $menuStock = MenuStock::where('menu_option_id', $option)->get();
                        if ($menuStock->isNotEmpty()) {
                            foreach ($menuStock as $stock_rs) {
                                $stock = Stock::find($stock_rs->stock_id);
                                $stock->amount = $stock->amount - ($stock_rs->amount * $rs['quantity']);
                                if ($stock->save()) {
                                    $log_stock = new LogStock();
                                    $log_stock->stock_id = $stock_rs->stock_id;
                                    $log_stock->order_id = $order->id;
                                    $log_stock->menu_option_id = $option;
                                    $log_stock->old_amount = $stock_rs->amount;
                                    $log_stock->amount = ($stock_rs->amount * $rs['quantity']);
                                    $log_stock->status = 2;
                                    $log_stock->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        event(new OrderCreated(['📦 มีออเดอร์ใหม่']));
        $data = [
            'status' => true,
            'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
        ];
    }
    return response()->json($data);
}

    public function sendEmp()
    {
        event(new OrderCreated(['ลูกค้าเรียกจากโต้ะที่ ' . session('table_id')]));
    }
    
    public function listorder()
    {
        $tableId = session('table_id');
        
        if (!$tableId && request()->has('table')) {
            $tableNumber = request()->get('table');
            $table = Table::where('table_number', $tableNumber)->first();
            if ($table) {
                $tableId = $table->id;
                session(['table_id' => $tableId]);
            }
        }
        
        $orderlist = collect([]); 
        
        if ($tableId) {
            $orderlist = Orders::where('table_id', $tableId)
                ->whereIn('status', [1, 2, 4, 5]) 
                ->orderBy('created_at', 'desc')
                ->get();
                
            if ($orderlist->count() > 0) {
                $sessionOrders = $orderlist->map(function($order) {
                    return [
                        'order_id' => $order->id,
                        'total' => $order->total
                    ];
                })->toArray();
                
                session(['orders' => $sessionOrders]);
            }
        }
        
        $config = Config::first();
        
        $total = $orderlist->sum('total');
        
        $qr_code = '';
        if ($config && $total > 0) {
            if ($config->promptpay) {
                $qr_code = PromptPayQRBuilder::staticMerchantPresentedQR($config->promptpay)
                    ->setAmount($total)
                    ->toSvgString();
            } elseif ($config->image_qr) {
                $qr_code = '<img width="100%" src="' . url('storage/' . $config->image_qr) . '">';
            }
        }
        
        return view('users.list_order', compact('orderlist', 'qr_code'));
    }
    
    public function listorderDetails(Request $request)
    {
        $groupedMenus = OrdersDetails::select('menu_id')
            ->where('order_id', $request->input('id'))
            ->groupBy('menu_id')
            ->get();
        $info = '';
        if ($groupedMenus->count() > 0) {
            foreach ($groupedMenus as $value) {
                $orderDetails = OrdersDetails::where('order_id', $request->input('id'))
                    ->where('menu_id', $value->menu_id)
                    ->with('menu', 'option')
                    ->get();
                $menuName = optional($orderDetails->first()->menu)->name ?? 'ไม่พบชื่อเมนู';
                $info .= '<div class="mb-3">';
                $info .= '<div class="row">';
                $info .= '<div class="col-auto d-flex align-items-start">';
                $info .= '</div>';
                $info .= '</div>';
                foreach ($orderDetails as $rs) {
                    $detailsText = $rs->option ? '+ ' . htmlspecialchars($rs->option->type) : '';
                    $priceTotal = number_format($rs->quantity * $rs->price, 2);
                    $info .= '<ul class="list-group mb-1 shadow-sm rounded">';
                    $info .= '<li class="list-group-item d-flex justify-content-between align-items-start">';
                    $info .= '<div class="">';
                    $info .= '<div><span class="fw-bold">' . htmlspecialchars($menuName) . '</span></div>';
                    if (!empty($detailsText)) {
                        $info .= '<div class="small text-secondary mb-1">' . $detailsText . '</div>';
                    }
                    $info .= '</div>';
                    $info .= '<div class="text-end d-flex flex-column align-items-end">';
                    $info .= '<div class="mb-1">จำนวน: ' . $rs->quantity . '</div>';
                    $info .= '<div>';
                    $info .= '<button class="btn btn-sm btn-primary">' . $priceTotal . ' บาท</button>';
                    $info .= '</div>';
                    $info .= '</div>';
                    $info .= '</li>';
                    $info .= '</ul>';
                }
                $info .= '</div>';
            }
        }
        echo $info;
    }
    
    public function confirmPay(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ไม่สามารถแนบสลิปได้',
        ];

        try {
            \Log::info('ConfirmPay Debug: ', [
                'session_orders' => session('orders', []),
                'session_table_id' => session('table_id'),
                'request_table_id' => $request->input('table_id'),
                'request_data' => $request->all(),
                'url_params' => request()->all()
            ]);

            $tableId = session('table_id');
            $orders = [];

            $sessionOrders = session('orders', []);
            
            if (empty($sessionOrders) || !$tableId) {
                if (!$tableId && $request->has('table_id')) {
                    $tableId = $request->input('table_id');
                }
                
                if (!$tableId && request()->has('table')) {
                    $tableNumber = request()->get('table');
                    $table = Table::where('table_number', $tableNumber)->first();
                    if ($table) {
                        $tableId = $table->id;
                        session(['table_id' => $tableId]);
                    }
                }

                // ดึงออเดอร์จาก database
                if ($tableId) {
                    $ordersFromDB = Orders::where('table_id', $tableId)
                        ->whereIn('status', [1, 2]) 
                        ->get();
                        
                    if ($ordersFromDB->count() > 0) {
                        $orders = $ordersFromDB->map(function($order) {
                            return [
                                'order_id' => $order->id,
                                'total' => $order->total
                            ];
                        })->toArray();
                    } else {
                        $allOrders = Orders::where('table_id', $tableId)
                            ->whereIn('status', [1, 2, 4, 5]) // รวมสถานะทั้งหมด
                            ->get();
                            
                        if ($allOrders->count() > 0) {
                            $orders = $allOrders->map(function($order) {
                                return [
                                    'order_id' => $order->id,
                                    'total' => $order->total
                                ];
                            })->toArray();
                        }
                    }
                }
            } else {
                $orders = $sessionOrders;
            }

            \Log::info('Orders found: ', [
                'table_id' => $tableId,
                'orders_count' => count($orders),
                'orders' => $orders
            ]);

            $remark = $request->input('remark');
            
            if (empty($orders)) {
                
                if ($tableId) {
                    $latestOrder = Orders::where('table_id', $tableId)
                        ->orderBy('created_at', 'desc')
                        ->first();
                        
                    if ($latestOrder) {
                        $orders = [[
                            'order_id' => $latestOrder->id,
                            'total' => $latestOrder->total
                        ]];
                    }
                }
                
                if (empty($orders)) {
                    $data['message'] = 'ไม่พบรายการสั่งอาหาร กรุณาสั่งอาหารก่อน (Table ID: ' . ($tableId ?? 'ไม่ระบุ') . ')';
                    return response()->json($data);
                }
            }

            if (!$tableId) {
                $data['message'] = 'ไม่พบข้อมูลโต้ะ';
                return response()->json($data);
            }

            // ตรวจสอบไฟล์สลิป
            if (!$request->hasFile('silp')) {
                $data['message'] = 'กรุณาแนบสลิปการโอนเงิน';
                return response()->json($data);
            }

            $file = $request->file('silp');
            
            // ตรวจสอบประเภทไฟล์
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                $data['message'] = 'กรุณาแนบไฟล์รูปภาพเท่านั้น (JPG, PNG)';
                return response()->json($data);
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                $data['message'] = 'ขนาดไฟล์ใหญ่เกินไป (สูงสุด 5MB)';
                return response()->json($data);
            }

            $filename = time() . '_table_' . $tableId . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('slips', $filename, 'public');

            // อัพเดทออเดอร์
            $updatedCount = 0;
            foreach ($orders as $orderData) {
                $orderModel = Orders::find($orderData['order_id']);
                if ($orderModel) {
                    $orderModel->status = 4; 
                    $orderModel->image = $path;
                    if ($remark) {
                        $orderModel->remark = $remark;
                    }
                    $orderModel->save();
                    $updatedCount++;
                }
            }

            if ($updatedCount === 0) {
                $data['message'] = 'ไม่สามารถอัพเดทออเดอร์ได้';
                return response()->json($data);
            }

            // ส่งการแจ้งเตือน
            $this->sendPaymentNotification($tableId, $orders);

            session()->forget(['orders', 'table_id']);

            $data = [
                'status' => true,
                'message' => 'แนบสลิปเรียบร้อยแล้ว รอการตรวจสอบจากเจ้าหน้าที่',
            ];

        } catch (\Exception $e) {
            \Log::error('ConfirmPay Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $data['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }

        return response()->json($data);
    }

    private function sendPaymentNotification($tableId, $orders)
    {
        try {
            $table = Table::find($tableId);
            $tableNumber = $table ? $table->table_number : 'ไม่ระบุ';
            
            $totalAmount = collect($orders)->sum('total');
            
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->insert([
                    'type' => 'payment',
                    'table_id' => $tableId,
                    'table_number' => $tableNumber,
                    'message' => "💳 มีการชำระเงินจาก โต้ะ {$tableNumber}",
                    'sub_message' => "ยอดเงิน: " . number_format($totalAmount, 2) . " บาท",
                    'amount' => $totalAmount,
                    'order_count' => count($orders),
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // ถ้าไม่มีตาราง notifications ให้ส่ง event แทน
                event(new OrderCreated(["💳 มีการชำระเงินจาก โต้ะ {$tableNumber} ยอดเงิน: " . number_format($totalAmount, 2) . " บาท"]));
            }
            
        } catch (\Exception $e) {
            \Log::error('Payment notification error: ' . $e->getMessage());
            
            // ถ้าเกิดข้อผิดพลาด ให้ส่ง event แทน
            try {
                $table = Table::find($tableId);
                $tableNumber = $table ? $table->table_number : 'ไม่ระบุ';
                $totalAmount = collect($orders)->sum('total');
                
                event(new OrderCreated(["💳 มีการชำระเงินจาก โต้ะ {$tableNumber} ยอดเงิน: " . number_format($totalAmount, 2) . " บาท"]));
            } catch (\Exception $e2) {
                \Log::error('Fallback payment notification error: ' . $e2->getMessage());
            }
        }
    }

    private function saveNotification($data)
    {
        DB::table('notifications')->insert($data);
    }

    /**
     * ตรวจสอบสถานะเมนู real-time
     */
    public function checkMenuAvailability(Request $request)
    {
        $menuIds = $request->input('menu_ids', []);
        
        $results = [];
        foreach ($menuIds as $menuId) {
            $menu = Menu::find($menuId);
            if ($menu) {
                $results[$menuId] = [
                    'available' => $menu->isAvailable(),
                    'message' => $menu->getAvailabilityMessage(),
                    'can_order' => $menu->isAvailable(),
                    'stock_quantity' => $menu->stock_quantity,
                    'is_out_of_stock' => $menu->is_out_of_stock
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * ดึงเมนูตามหมวดหมู่ที่พร้อมขาย
     */
    public function getAvailableMenus($categoryId)
    {
        $menus = Menu::where('categories_id', $categoryId)
                    ->availableNow()
                    ->with(['files', 'typeOptions.options'])
                    ->orderBy('name')
                    ->get();

        $menus->each(function($menu) {
            $menu->availability_status = $menu->getAvailabilityMessage();
            $menu->can_order = $menu->isAvailable();
        });

        return response()->json($menus);
    }

    /**
     * ตรวจสอบสถานะหมวดหมู่
     */
    public function checkCategoryAvailability(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);
        
        $results = [];
        foreach ($categoryIds as $categoryId) {
            $category = Categories::find($categoryId);
            if ($category) {
                $totalMenus = Menu::where('categories_id', $categoryId)->count();
                $availableMenus = Menu::where('categories_id', $categoryId)->availableNow()->count();
                
                // กำหนดสถานะ
                $hasAvailableMenus = $availableMenus > 0;
                $statusText = 'พร้อมขาย';
                $statusClass = 'bg-success';
                $indicatorClass = 'available';
                
                if ($availableMenus == 0) {
                    $statusText = 'ปิดขาย';
                    $statusClass = 'bg-danger';
                    $indicatorClass = 'unavailable';
                } elseif ($availableMenus < $totalMenus) {
                    $statusText = 'บางรายการ';
                    $statusClass = 'bg-warning text-dark';
                    $indicatorClass = 'limited';
                }
                
                $results[$categoryId] = [
                    'has_available_menus' => $hasAvailableMenus,
                    'available_count' => $availableMenus,
                    'total_count' => $totalMenus,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                    'indicator_class' => $indicatorClass
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * ดึงสถิติเมนูทั้งหมด
     */
    public function getMenuStatistics()
    {
        $stats = [
            'total_categories' => Categories::count(),
            'available_categories' => Categories::whereHas('menu', function($query) {
                $query->availableNow();
            })->count(),
            'total_menus' => Menu::count(),
            'available_menus' => Menu::availableNow()->count(),
            'out_of_stock_menus' => Menu::where('is_out_of_stock', 1)->count(),
            'time_restricted_menus' => Menu::where('has_time_restriction', 1)->count()
        ];

        return response()->json($stats);
    }

   
    public function getUpcomingMenus()
    {
        $now = Carbon::now();
        $nextHour = $now->copy()->addHour();
        
        $upcomingMenus = Menu::where('has_time_restriction', 1)
                            ->where('is_active', 1)
                            ->where('is_out_of_stock', 0)
                            ->where(function($query) use ($now, $nextHour) {
                                $query->where(function($q) use ($now, $nextHour) {
                                    $q->whereTime('available_from', '>', $now->format('H:i:s'))
                                      ->whereTime('available_from', '<=', $nextHour->format('H:i:s'));
                                });
                            })
                            ->with(['category', 'files'])
                            ->get();

        return response()->json($upcomingMenus);
    }
}