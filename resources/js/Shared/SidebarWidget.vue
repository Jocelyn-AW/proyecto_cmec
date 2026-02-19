<template>
  <div
    class="mx-auto mb-10 w-full max-w-60 rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]"
  >
    <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">#1 Tailwind CSS Dashboard</h3>
    <p class="mb-4 text-gray-500 text-theme-sm dark:text-gray-400">
      Leading Tailwind CSS Admin Template with 400+ UI Component and Pages.
    </p>
    <a
      href="https://tailadmin.com/pricing"
      target="_blank"
      rel="nofollow"
      class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600"
    >
      Purchase Plan
    </a>
  </div>
</template>

<script setup lang="ts">
const menuGroups = [
    {
        title: "Menu",
        items: [
            {
                icon: GridIcon,
                name: "Dashboard",
                subItems: [{ name: "Ecommerce", path: "/dashboard", pro: false }],
            },
            {
                icon: BoxCubeIcon,
                name: "Banners",
                path: "/banners",
            },
            {
                icon: Message2Line,
                name: "Publicidad",
                path: "/publicity",
            },
            {
                icon: DocsIcon,
                name: "Administración de Usuarios",
                path: "/users"
            },
            {
                icon: CalenderIcon,
                name: "Calendar",
                path: "/calendar",
            },
            {
                icon: UserCircleIcon,
                name: "User Profile",
                path: "/profile",
            },

            {
                name: "Forms",
                icon: ListIcon,
                subItems: [
                    { name: "Form Elements", path: "/form-elements", pro: false },
                ],
            },
            {
                name: "Tables",
                icon: TableIcon,
                subItems: [{ name: "Basic Tables", path: "/basic-tables", pro: false }],
            },
            {
                name: "Pages",
                icon: PageIcon,
                subItems: [
                    { name: "Black Page", path: "/blank", pro: false },
                    { name: "404 Page", path: "/error-404", pro: false },
                ],
            },
        ],
    },
    {
        title: "Others",
        items: [
            {
                icon: PieChartIcon,
                name: "Charts",
                subItems: [
                    { name: "Line Chart", path: "/line-chart", pro: false },
                    { name: "Bar Chart", path: "/bar-chart", pro: false },
                ],
            },
            {
                icon: BoxCubeIcon,
                name: "Ui Elements",
                subItems: [
                    { name: "Alerts", path: "/alerts", pro: false },
                    { name: "Avatars", path: "/avatars", pro: false },
                    { name: "Badge", path: "/badge", pro: false },
                    { name: "Buttons", path: "/buttons", pro: false },
                    { name: "Images", path: "/images", pro: false },
                    { name: "Videos", path: "/videos", pro: false },
                ],
            },
            {
                icon: PlugInIcon,
                name: "Authentication",
                subItems: [
                    { name: "Signin", path: "/signin", pro: false },
                    { name: "Signup", path: "/signup", pro: false },
                ],
            },
            // ... Add other menu items here
        ],
    },
];
</script>
