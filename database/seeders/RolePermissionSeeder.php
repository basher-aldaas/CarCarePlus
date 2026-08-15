<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. إنشاء الأدوار (Create Roles)
        $superAdminRole = Role::findOrCreate('super_admin', 'web');
        $adminRole      = Role::findOrCreate('admin', 'web');
        $workshopRole   = Role::findOrCreate('workshop', 'web');
        $custPersonal   = Role::findOrCreate('customer_personal', 'web');
        $custCompany    = Role::findOrCreate('customer_company', 'web');
        $empWasher      = Role::findOrCreate('employee_washer', 'web');
        $empMechanic    = Role::findOrCreate('employee_mechanic', 'web');

        // 2. إنشاء كل الصلاحيات دفعة واحدة (تجميع كافة المصفوفات بدون استثناء)
        $allPermissions = array_unique(array_merge(
            $this->superAdminPermissions(),
            $this->adminPermissions(),
            $this->workshopPermissions(),
            $this->customerPersonalPermissions(),
            $this->customerCompanyPermissions(),
            $this->employeeWasherPermissions(),
            $this->employeeMechanicPermissions(),
        ));

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 3. ربط الصلاحيات بالأدوار (Sync Permissions)
        $superAdminRole->syncPermissions($this->superAdminPermissions());
        $adminRole->syncPermissions($this->adminPermissions());
        $workshopRole->syncPermissions($this->workshopPermissions());
        $custPersonal->syncPermissions($this->customerPersonalPermissions());
        $custCompany->syncPermissions($this->customerCompanyPermissions());
        $empWasher->syncPermissions($this->employeeWasherPermissions());
        $empMechanic->syncPermissions($this->employeeMechanicPermissions());
    }

    // ==========================================
    // مصفوفات الصلاحيات كدوال لمنع أخطاء الـ Seeder
    // ==========================================

    private function superAdminPermissions(): array
    {
        return [
            // === إدارة المستخدمين والأدوار ===
            'add.admin', 'edit.admin', 'delete.admin', 'show.admins',
            'delete.customer', 'show.customers', 'show.users',
            'add.workshop', 'edit.workshop', 'delete.workshop', 'show.workshops',
            'add.company', 'edit.company', 'delete.company', 'show.companies',
            'add.employee', 'edit.employee', 'delete.employee', 'show.employees',
            'manage.user_status',
            'manage.roles', 'manage.permissions',
            'show.otp_codes', 'show.audit_logs',
            'add.staff_account', 'show.registration_requests', 'manage.registration_requests',

// === إدارة العملاء الشخصيين ===
            'show.personal_customers',
            'edit.personal_customers',
            'delete.personal_customers',

// === إدارة عملاء الشركات ===
            'show.company_customers',
            'edit.company_customers',
            'delete.company_customers',

            // === الشركات والفروع والموظفين ===
            'add.branch', 'edit.branch', 'delete.branch', 'show.branches',

            // === السيارات وأنواعها ===
            'manage.car_types', 'show.car_types',
            'manage.car_brands', 'show.car_brands',
            'add.car', 'edit.car', 'delete.car', 'show.car', 'show.cars', 'show.client.cars',

            // === الخدمات والأسعار والتصنيفات ===
            'manage.categories', 'show.categories',
            'manage.services', 'show.services',
            'manage.sub_services', 'show.sub_services',
            'add.pricing_rule', 'edit.pricing_rule', 'delete.pricing_rule', 'show.pricing_rules',
            'add.pricing_rule_types', 'edit.pricing_rule_types', 'delete.pricing_rule_types', 'show.pricing_rule_types',

            // === المواد، المخزون، والمشتريات ===
            'add.material', 'edit.material', 'delete.material', 'show.materials','show.material',
            'manage.material_units',
            'manage.inventory', 'show.inventory',
            'show.inventory_transactions',
            'manage.purchase_requests', 'approve.purchase_request', 'reject.purchase_request',
            'manage.purchase_request_items',
            'manage.purchase_payments',

            // === الباقات والاشتراكات ===
            'manage.packages', 'show.packages',
            'manage.package_services', 'show.package_services',
            'manage.package_service_sub_services', 'show.package_service_sub_services',
            'manage.user_packages', 'show.user_packages', 'add.user_package', 'edit.user_package',

            // === الطلبات والعمليات الميدانية ===
            'edit.order', 'cancel.order', 'assign.order', 'discount.order', 'show.orders',
            'show.order_sub_services',
            'show.order_materials',
            'show.order_status_history',
            'show.order_price_items',
            'show.employee_reports',
            'show.spare_part_requests',
            'show.gps_tracking', 'show.gps_logs',

            // === المساعدة على الطريق وأعطال السيارات ===
            'show.road_assistance_details', 'manage.road_assistance_details',
            'show.maintenance_details', 'manage.maintenance_details',
            'show.towing_details', 'manage.towing_details',
            'manage.problem_types', 'show.problem_types',
            'manage.suggested_problems', 'show.suggested_problems',

            // === النزاعات والجدولة ===
            'show.scheduling_conflicts', 'resolve.scheduling_conflict',

            // === المدفوعات، المحافظ، والنقاط ===
            'show.payments','show.payment',
            'create.refund', 'show.refunds', 'edit.refund_status',
            'adjust.wallet_balance',
            'show.wallet_transactions',
            'manage.point_config', 'show.point_config', 'show.user_points', 'show.all_user_points', 'show.points_transactions',
            'add.points.manual',
            'show.wallets',

            // === التقييمات، التقارير، وإعدادات النظام ===
            'show.ratings',
            'show.reports', 'show.financial_reports',
            'add.ai_rule', 'edit.ai_rule', 'delete.ai_rule', 'show.ai_rules',
            'manage.system_setting', 'show.system_settings',
            'browse.workshops',
        ];
    }

    private function adminPermissions(): array
    {
        return [
            // === إدارة المستخدمين والموظفين (ضمن نطاقه) ===
            'show.customers', 'show.users',
            'add.employee', 'edit.employee', 'show.employees',
            'manage.user_status',
            'show.otp_codes',
            'show.personal_customers',

            // === الشركات والفروع والسيارات ===
            'show.companies',
            'edit.branch', 'show.branches',
            'show.car_types',
            'show.car_brands',
            'add.car', 'edit.car', 'show.car', 'show.cars', 'show.client.cars',
            'show.ai_rules',
            // === الخدمات والأسعار (عرض فقط) ===
            'show.categories', 'show.services', 'show.sub_services', 'show.pricing_rules',
            'discount.order',
            // === المواد، المخزون، والمشتريات (صلاحيات كاملة للفرع) ===
            'show.materials','show.material',
            'show.material_units',
            'manage.inventory', 'show.inventory',
            'show.inventory_transactions',
            'manage.purchase_requests', 'manage.purchase_request_items','add.purchase_requests','edit.purchase_requests','delete.purchase_requests',
            'manage.purchase_payments',
            'show.system_settings',
            // === الباقات والاشتراكات ===
            'show.packages', 'show.package_services', 'show.package_service_sub_services',
            'show.user_packages', 'add.user_package', 'edit.user_package',

            // === إدارة العمليات والطلبات ===
            'edit.order', 'cancel.order', 'assign.order', 'show.orders',
            'show.order_sub_services', 'show.order_materials', 'show.order_status_history', 'show.order_price_items',
            'show.employee_reports',
            'show.spare_part_requests',
            'show.gps_tracking', 'show.gps_logs',

            // === المساعدة على الطريق والنزاعات ===
            'show.road_assistance_details', 'manage.road_assistance_details', 'show.problem_types', 'show.suggested_problems',
            'show.maintenance_details', 'manage.maintenance_details',
            'show.towing_details', 'manage.towing_details',
            'show.scheduling_conflicts', 'resolve.scheduling_conflict',

            // === المالية، المحافظ، والنقاط ===
            'show.payments','show.payment', 'create.refund', 'show.refunds',
            'adjust.wallet_balance', 'show.wallet_transactions',
            'show.point_config', 'show.user_points', 'show.all_user_points', 'show.points_transactions',
            'show.wallets',
            // === الورش ===
            'show.workshops',

            // === التقييمات والتقارير ===
            'show.ratings', 'show.reports',

            'show.pricing_rule_types',
            'browse.workshops',

        ];
    }

    private function workshopPermissions(): array
    {
        return [
            // تصفح الورش الخاصة به والتعديل عليها بحسب النطاق البرمجي
            'edit.workshop', 'show.workshops',
            'show.car',
            'show.car_types',
            'show.car_brands',

            // إدارة فنيي الورشة
            'add.employee', 'edit.employee', 'show.employees',

            // الخدمات والأسعار المتاحة له للاطلاع
            'show.categories', 'show.services', 'show.sub_services',

            // إدارة فواتير وطلبات الورشة المسندة إليها
              'edit.order',
             'show.car_service_history',

            // تفاصيل الطلبات الميدانية (صيانة / مساعدة على الطريق / سحب)
            'show.maintenance_details', 'manage.maintenance_details',
            'show.road_assistance_details', 'manage.road_assistance_details',

            // العمليات والتقارير المالية
            'show.employee_reports',
            'show.ratings',
            'show.orders',
            'browse.workshops',
            'show.material',
        ];
    }

    private function customerPersonalPermissions(): array
    {
        return [
            'add.car', 'edit.car', 'delete.car', 'show.car', 'show.client.cars',
            'show.users',
            'show.branches', 'show.categories', 'show.services', 'show.sub_services', 'show.packages', 'show.package_services',
            'browse.workshops',
            'create.order', 'cancel.order', 'show.orders', 'show.order_sub_services', 'show.order_status_history', 'show.order_price_items', 'show.gps_tracking',
            'create.road_assistance', 'show.road_assistance_details', 'show.maintenance_details', 'show.towing_details', 'show.suggested_problems', 'manage.ai_chat',
            'show.wallet', 'show.wallet_transactions', 'show.user_packages', 'add.user_package', 'edit.user_package',
            'show.user_points', 'show.points_transactions',
            'create.payment', 'show.payments','show.payment', 'create.refund', 'show.refunds', 'create.rating', 'show.ratings',
            'show.spare_part_requests', 'approve.spare_part_request', 'reject.spare_part_request',
            'show.notifications', 'edit.notification_status',
            'show.car_types',
            'show.car_brands',
            'show.package_service_sub_services',
            'show.pricing_rule_types',
            'show.pricing_rules',
            'show.client.cars',
            'show.order_materials',
            'show.material',
        ];
    }

    private function customerCompanyPermissions(): array
    {
        return array_merge($this->customerPersonalPermissions(), [
            'show.companies', 'edit.company',
            'manage.fleet_cars',
            'show.reports',
        ]);
    }

    private function employeeWasherPermissions(): array
    {
        return [
            'show.employees',
            'show.car',
            'show.orders', 'edit.order', 'show.order_sub_services', 'show.order_status_history', 'show.order_price_items',
            'manage.gps_logs',
            'show.categories', 'show.services', 'show.sub_services', 'show.materials','show.material', 'manage.order_materials',
            'show.material_units',

            'create.employee_report', 'show.employee_reports', 'show.ratings',
            'confirm.cash_payment',
            'show.car_types',
            'show.car_brands',
            'show.pricing_rule_types',
            'show.pricing_rules',
            'show.client.cars',
            'show.order_materials',
            'browse.workshops',
            'show.payment',
        ];
    }

    private function employeeMechanicPermissions(): array
    {
        return [
            'show.employees',
            'show.car',
            'show.orders', 'edit.order', 'show.order_sub_services', 'show.order_status_history', 'show.order_price_items',
            'show.road_assistance_details', 'manage.road_assistance_details', 'show.suggested_problems', 'manage.ai_chat',
            'show.maintenance_details', 'manage.maintenance_details',
            'show.towing_details', 'manage.towing_details',
            'confirm.cash_payment',
            'manage.gps_logs',
            'show.categories', 'show.services', 'show.sub_services', 'show.materials','show.material', 'manage.order_materials',
            'create.employee_report', 'show.employee_reports', 'create.spare_part_request', 'show.spare_part_requests',
            'show.ratings',
            'show.car_types',
            'show.car_brands',
            'show.pricing_rule_types',
            'show.pricing_rules',
            'show.client.cars',
            'show.order_materials',
            'browse.workshops',
            'show.payment',
        ];
    }
}
