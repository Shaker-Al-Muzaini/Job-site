// resources/js/Pages/Auth/Profile.tsx
import React from "react";
import { router } from "@inertiajs/react";
import AuthLayout from "@/Layouts/AuthLayout";

interface User {
    name: string;
    email: string;
    avatar?: string;
}

interface Props {
    user: User;
    success?: string;
}

export default function Profile({ user, success }: Props) {

    if (!user) {
        return (
            <div className="p-6 text-red-500 text-center">
                Error: User data is not available.
            </div>
        );
    }

    const logout = () => {
        router.post("/logout");
    };

    return (
        <div className="max-w-2xl w-full bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div className="p-6 bg-gray-700">

                {success && (
                    <div className="text-green-400 mb-4">{success}</div>
                )}

                <div className="flex items-center gap-6">
                    <img
                        src={user.avatar || "https://i.pravatar.cc/150"}
                        className="w-24 h-24 rounded-full border-4 border-gray-500"
                        alt="Profile"
                    />
                    <div>
                        <h2 className="text-2xl font-bold">{user.name}</h2>
                        <p className="text-gray-300">{user.email}</p>
                        <button
                            onClick={logout}
                            className="mt-4 px-4 py-2 bg-red-600 rounded-lg hover:bg-red-700 transition"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

// Layout pattern
Profile.layout = (page: React.ReactNode) => <AuthLayout>{page}</AuthLayout>;
