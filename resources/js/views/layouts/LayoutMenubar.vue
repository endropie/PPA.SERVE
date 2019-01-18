<template>
     <aside class="main-sidebar sidebar-mini sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="/home" class="brand-link">
            <img src="/adminlte3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8" />
            <span class="brand-text font-weight-light">GDS VIRMATA</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="/adminlte3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#"> Administrator </a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column mb-5" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-th-large"></i>
                        <p>Common<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/common/items" class="nav-link ">
                                <i class="nav-icon fas fa-cubes"></i>
                                <p class="text">Items</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/common/samples" class="nav-link ">
                                <i class="nav-icon fas fa-cube"></i>
                                <p class="text">Samples</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-money-bill-alt"></i>
                        <p>Incomes<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/incomes/customers" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Customers</p>
                            </router-link>
                            <router-link  to="/admin/test" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Delivery Order</p>
                            </router-link>
                            <router-link  to="/admin/incomes/invoices" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Invoices</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Expenses<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/expenses/vendors" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Vendors</p>
                            </router-link>
                        <li class="nav-item">
                            <router-link  to="/admin/expenses/bills" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Bills</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-industry"></i>
                        <p>Manufacture<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/test" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">SPK</p>
                            </router-link>
                            <router-link  to="/admin/test" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Work-in</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-warehouse"></i>
                        <p>Warehouse<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/warehouses/incoming_goods" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Incoming Goods</p>
                            </router-link>
                            <router-link  to="/admin/warehouses/finished_goods" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Finished Goods</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-tools"></i>
                        <p>References<i class="fa fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link  to="/admin/references/brands" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Agent of Brands
                                    <span class="badge badge-primary right">ATPM</span>
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/references/specifications" class="nav-link ">
                                 <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Specification</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/references/units" class="nav-link ">
                                 <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Units</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/references/categories" class="nav-link ">
                                 <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Categories</p>
                            </router-link>
                        </li>
                        
                        <li class="nav-item">
                            <router-link  to="/admin/references/colours" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Colours</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/references/ordertypes" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Order Types</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link  to="/admin/references/marketplaces" class="nav-link ">
                                <i class="nav-icon fas fa-angle-double-right"></i>
                                <p class="text">Marketplaces</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
            </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
     </aside>
</template>
<script>
export default {
    name: 'layout-menubar'
}
</script>


