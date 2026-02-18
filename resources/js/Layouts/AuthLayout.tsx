import { Link } from "@inertiajs/react";
import type { ReactNode } from "react";

type Props = {
    children: ReactNode;
};

export default function AuthLayout({ children }: Props) {
    return (
        <div style={{ padding: "2rem", fontFamily: "Arial, sans-serif" }}>
            <header style={{ marginBottom: "2rem" }}>
                <h2>لوحة Auth</h2>
                <nav style={{ display: "flex", gap: "1rem" }}>
                    <Link href="/login">تسجيل دخول</Link>
                    <Link href="/register">تسجيل جديد</Link>
                    <Link href="/profile">البروفايل</Link>
                </nav>
            </header>
            <main>{children}</main>
            <footer style={{ marginTop: "2rem", borderTop: "1px solid #ccc", paddingTop: "1rem" }}>
                حقوق الطبع محفوظة © 2026
            </footer>
        </div>
    );
}
