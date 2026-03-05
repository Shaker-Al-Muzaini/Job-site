// resources/js/Layouts/AuthLayout.tsx
import { Link } from "@inertiajs/react";
import type { ReactNode } from "react";

type Props = {
    children: ReactNode;
};

export default function AuthLayout({ children }: Props) {
    return (
        <div className="min-h-screen flex flex-col bg-gray-900 text-white font-sans">

            <header className="bg-gray-800 shadow p-6">
                <div className="max-w-6xl mx-auto flex justify-between items-center">
                    <h1 className="text-xl font-bold">Auth Panel</h1>

                    <nav className="flex gap-6 text-sm">
                        <Link href="/login" className="hover:text-gray-300">
                            تسجيل دخول
                        </Link>
                        <Link href="/register" className="hover:text-gray-300">
                            تسجيل جديد
                        </Link>
                        <Link href="/profile" className="hover:text-gray-300">
                            البروفايل
                        </Link>
                    </nav>
                </div>
            </header>

            <main className="flex-1 flex items-center justify-center p-6">
                {children}
            </main>

            <footer className="text-center text-sm text-gray-400 p-4 border-t border-gray-700">
                © 2026 — جميع الحقوق محفوظة
            </footer>
        </div>
    );
}
