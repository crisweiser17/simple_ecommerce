<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800"><?php echo __('Contact Us'); ?></h1>

        <?php if (!empty($contactSuccessMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($contactSuccessMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($contactErrorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($contactErrorMessage); ?>
            </div>
        <?php endif; ?>

        <form action="/contact/send" method="POST" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Full Name'); ?></label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($contactData['name'] ?? ''); ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2"
                        placeholder="<?php echo __('Full Name'); ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Email'); ?></label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($contactData['email'] ?? ''); ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2"
                        placeholder="<?php echo __('Email'); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Phone'); ?></label>
                    <input type="text" name="phone" required value="<?php echo htmlspecialchars($contactData['phone'] ?? ''); ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2"
                        placeholder="<?php echo __('Phone'); ?>" oninput="maskPhone(event)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Subject'); ?></label>
                    <select name="subject" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2 bg-white">
                        <option value=""><?php echo __('Select a subject'); ?></option>
                        <?php foreach ($contactSubjects as $subjectKey): ?>
                            <option value="<?php echo htmlspecialchars($subjectKey); ?>" <?php echo (($contactData['subject'] ?? '') === $subjectKey) ? 'selected' : ''; ?>>
                                <?php echo __('contact_subject_' . $subjectKey); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Message'); ?></label>
                <textarea name="message" rows="6" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2"
                    placeholder="<?php echo __('Write your message'); ?>"><?php echo htmlspecialchars($contactData['message'] ?? ''); ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                    <?php echo __('Send Message'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const phoneInput = document.querySelector('input[name="phone"]');
        if (phoneInput && phoneInput.value && typeof maskPhone === 'function') {
            maskPhone({ target: phoneInput });
        }
    });
</script>
