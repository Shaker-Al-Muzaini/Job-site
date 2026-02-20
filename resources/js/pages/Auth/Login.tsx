import { useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import React from 'react';

export default function Login() {
    const form = useForm({ email: '', password: '' });
    const { errors } = usePage().props as any; // أخطاء قادمة من Inertia

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/login');
    }

    return (
        <AuthLayout>
            <div className="fixed inset-0 flex items-center justify-center bg-gray-900 overflow-hidden">
                <div className="w-full max-w-md p-8 rounded-2xl shadow-2xl bg-white/5 backdrop-blur-lg border border-white/10">
                    <h2 className="text-3xl font-bold text-center text-white mb-6">
                        تسجيل الدخول
                    </h2>

                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block mb-2 text-sm text-gray-300">البريد الإلكتروني</label>
                            <input
                                type="email"
                                value={form.data.email}
                                onChange={e => form.setData('email', e.target.value)}
                                className="w-full p-3 rounded-lg bg-gray-900 text-white border border-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none"
                                required
                            />
                            {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                        </div>

                        <div>
                            <label className="block mb-2 text-sm text-gray-300">كلمة المرور</label>
                            <input
                                type="password"
                                value={form.data.password}
                                onChange={e => form.setData('password', e.target.value)}
                                className="w-full p-3 rounded-lg bg-gray-900 text-white border border-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none"
                                required
                            />
                            {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
                        </div>

                        <button
                            type="submit"
                            className="w-full py-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 transition font-semibold text-white shadow-lg"
                        >
                            دخول
                        </button>

                        <p className="text-center text-sm text-gray-400 mt-3">
                            ليس لديك حساب؟{" "}
                            <a href="/register" className="text-indigo-400 hover:underline">إنشاء حساب</a>
                        </p>
                    </form>
                </div>
            </div>
        </AuthLayout>
    );
}
