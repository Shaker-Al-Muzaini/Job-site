import { Link } from "@inertiajs/react";
import type { ReactNode } from "react";

type Props = {
    children: ReactNode;
};

export default function AuthLayout({ children }: Props) {
    return (
        <div className="flex flex-col min-h-screen bg-gray-900 text-white font-sans">
            <header className="p-6 bg-gray-800 shadow-md">
                <h2 className="text-2xl font-bold">لوحة Auth</h2>
                <nav className="flex gap-4 mt-2">
                    <Link href="/login" className="hover:underline">
                        تسجيل دخول
                    </Link>
                    <Link href="/register" className="hover:underline">
                        تسجيل جديد
                    </Link>
                    <Link href="/profile" className="hover:underline">
                        البروفايل
                    </Link>
                </nav>
            </header>

            {/* هذه التعديلات تجعل المحتوى في الوسط بدون scroll إلا عند الحاجة */}
            <main className="flex-1 flex items-center justify-center p-6">
                {children}
            </main>

            <footer className="p-4 text-center border-t border-gray-700">
                حقوق الطبع محفوظة © 2026
            </footer>
        </div>
    );
}
