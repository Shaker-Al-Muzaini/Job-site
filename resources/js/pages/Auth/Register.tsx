import { useForm, Link } from "@inertiajs/react";
import React from 'react';
import AuthLayout from "@/Layouts/AuthLayout";

export default function Register() {
    // استخدام useForm من Inertia لإدارة البيانات والأخطاء
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
    });

    // دالة الإرسال
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post("/register"); // route POST في Laravel لمعالجة التسجيل
    };

    return (
        <AuthLayout>
            <div className="fixed inset-0 flex items-center justify-center bg-gray-900 overflow-hidden">
                <div className="w-full max-w-sm p-6 space-y-6 bg-gray-800 rounded-lg shadow-lg">
                    <h2 className="text-2xl font-bold text-center">Register</h2>
                    <form  onSubmit={submit} className="space-y-4">
                            {/* Name */}
                            <div>
                                <label htmlFor="name" className="block mb-1 text-sm font-medium">
                                    Name
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData("name", e.target.value)}
                                    autoFocus
                                    className="w-full p-2 rounded bg-gray-700 text-gray-100 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.name && <span className="text-red-500 text-xs">{errors.name}</span>}
                            </div>

                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block mb-1 text-sm font-medium">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    className="w-full p-2 rounded bg-gray-700 text-gray-100 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.email && <span className="text-red-500 text-xs">{errors.email}</span>}
                            </div>

                            {/* Password */}
                            <div>
                                <label htmlFor="password" className="block mb-1 text-sm font-medium">
                                    Password
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    value={data.password}
                                    onChange={(e) => setData("password", e.target.value)}
                                    className="w-full p-2 rounded bg-gray-700 text-gray-100 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.password && <span className="text-red-500 text-xs">{errors.password}</span>}
                            </div>

                            {/* Password Confirmation */}
                            <div>
                                <label htmlFor="password_confirmation" className="block mb-1 text-sm font-medium">
                                    Confirm Password
                                </label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData("password_confirmation", e.target.value)}
                                    className="w-full p-2 rounded bg-gray-700 text-gray-100 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-2 mt-4 bg-blue-600 rounded font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Register
                            </button>

                            {/* Link to Login */}
                            <p className="mt-3 text-xs text-center text-gray-300">
                                Already have an account?{" "}
                                <Link href="/login" className="text-blue-400 hover:underline">
                                    Login
                                </Link>
                            </p>

                    </form>
                </div>
            </div>
        </AuthLayout>

    );
}
