<template>
    <!-- Control sidebar content goes here -->
    <div>
        
    <!-- Navbar -->
        <layout-navbar></layout-navbar>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
        <layout-menubar></layout-menubar>
    

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
         <v-app id="inspire">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <header-title>
                                <h1 class="m-0 text-dark">Header</h1>
                        </header-title>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <breadcrumbs></breadcrumbs>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <router-view></router-view>
                    </div>
                </div>            
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content -->
         </v-app>
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <layout-sidebar></layout-sidebar>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer" style="padding:5px 15px">
            <layout-footer></layout-footer>
        </footer>
    </div>
    <!-- ./wrapper -->

</template>
<script>
import Breadcrumbs from '@/components/ContentBreadcrumbs'
import HeaderTitle from '@/components/ContentTitle'

export default {
    name: 'layout-app',
    components: {
        'header-title' : HeaderTitle,
        'breadcrumbs'  : Breadcrumbs,
    },
    created() {
        // console.log('AdminLTE Create-Mounted')
    }
}
</script>


