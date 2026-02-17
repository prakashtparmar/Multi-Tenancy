<div x-data="chatWidget()" x-init="init()" x-cloak class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

    <!-- Chat Window -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="mb-4 w-80 sm:w-96 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[500px]">

        <!-- Header -->
        <div class="bg-primary text-primary-foreground p-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-2">
                <template
                    x-if="currentView === 'chat' || currentView === 'create_group' || currentView === 'edit_group'">
                    <button @click="setView('list')"
                        class="hover:bg-primary/20 p-1 rounded-full transition-colors mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-arrow-left">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" />
                        </svg>
                    </button>
                </template>
                <h3 class="font-semibold text-lg" x-text="headerTitle">Chat</h3>
            </div>
            <div class="flex items-center gap-1">
                <template x-if="currentView === 'chat' && currentUser && currentUser.group_id != '0'">
                    <button @click="setView('edit_group')"
                        class="hover:bg-primary/20 p-1 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-settings">
                            <path
                                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.1a2 2 0 0 1-1-1.72v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </template>
                <button @click="toggleChat()" class="hover:bg-primary/20 p-1 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- User List View -->
        <div x-show="currentView === 'list'" class="flex-1 overflow-y-auto p-2 space-y-1 relative">
            <div class="p-2 sticky top-0 bg-white dark:bg-zinc-900 z-10">
                <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchUsers()"
                    placeholder="Search users..."
                    class="w-full px-3 py-2 text-sm rounded-md border border-input bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring">
            </div>

            <div class="px-2 pb-2">
                <button @click="setView('create_group')"
                    class="w-full py-2 px-3 bg-primary/10 text-primary hover:bg-primary/20 rounded-md text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-users">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Create New Group
                </button>
            </div>

            <template x-if="loadingUsers">
                <div class="flex justify-center p-4">
                    <svg class="animate-spin h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </template>

            <template x-for="user in users" :key="user.group_id != '0' ? 'g' + user.id : 'u' + user.id">
                <button @click="selectUser(user)"
                    class="w-full flex items-center gap-3 p-3 hover:bg-muted/50 rounded-lg transition-colors text-left group">
                    <div class="relative">
                        <template x-if="user.photo">
                            <img :src="user.photo" class="w-10 h-10 rounded-full object-cover border border-border">
                        </template>
                        <template x-if="!user.photo">
                            <div
                                class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold border border-border">
                                <span x-text="getInitials(user.name)"></span>
                            </div>
                        </template>

                        <!-- Online/Offline Indicator -->
                        <span
                            class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-zinc-900"
                            :class="user.is_online ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <p
                                class="font-medium text-sm truncate text-foreground group-hover:text-primary transition-colors">
                                <span x-text="user.name"></span>
                                <span x-show="user.location" x-text="' (' + user.location + ')'"
                                    class="text-xs text-muted-foreground ml-1"></span>
                            </p>
                            <span class="text-[10px] text-muted-foreground"
                                x-text="user.last_message_time || ''"></span>
                        </div>
                        <p class="text-xs text-muted-foreground truncate"
                            x-text="user.last_message || 'No messages yet'"></p>
                    </div>
                    <template x-if="user.unread_msg > 0">
                        <span
                            class="bg-primary text-primary-foreground text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                            x-text="user.unread_msg"></span>
                    </template>
                </button>
            </template>
        </div>

        <!-- Create Group View -->
        <div x-show="currentView === 'create_group'"
            class="flex-1 flex flex-col h-full bg-background p-4 overflow-y-auto">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium mb-1 block">Group Name</label>
                    <input type="text" x-model="newGroupName" placeholder="Enter group name..."
                        class="w-full px-3 py-2 text-sm rounded-md border border-input bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring">
                </div>

                <div class="flex-1">
                    <label class="text-sm font-medium mb-1 block">Select Members</label>
                    <input type="text" x-model="userSearchQuery" @input.debounce.300ms="searchUsersForGroup()"
                        placeholder="Search users..."
                        class="w-full px-3 py-2 text-sm rounded-md border border-input bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring mb-2">

                    <div class="max-h-48 overflow-y-auto border rounded-md p-2 space-y-1">
                        <template x-for="user in availableUsers" :key="user.id">
                            <label class="flex items-center gap-2 p-2 hover:bg-muted/50 rounded cursor-pointer">
                                <input type="checkbox" :value="user.id" x-model="selectedGroupMembers"
                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm"
                                    x-text="user.name + (user.location ? ' (' + user.location + ')' : '')"></span>
                            </label>
                        </template>
                        <template x-if="availableUsers.length === 0">
                            <p class="text-xs text-muted-foreground text-center py-2">No users found</p>
                        </template>
                    </div>
                </div>

                <button @click="createGroup()" :disabled="!newGroupName || selectedGroupMembers.length === 0"
                    class="w-full py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Create Group
                </button>
            </div>
        </div>

        <!-- Edit Group View -->
        <div x-show="currentView === 'edit_group'"
            class="flex-1 flex flex-col h-full bg-background p-4 overflow-y-auto">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium mb-1 block">Group Name</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="editGroupName"
                            class="flex-1 px-3 py-2 text-sm rounded-md border border-input bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring">
                        <button @click="updateGroupName()"
                            class="px-3 py-2 bg-primary text-primary-foreground rounded-md text-xs font-medium">Update</button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium mb-1 block">Manage Members</label>
                    <div class="max-h-40 overflow-y-auto border rounded-md p-2 mb-2 space-y-1">
                        <template x-for="member in groupMembers" :key="member.id">
                            <div class="flex items-center justify-between p-2 hover:bg-muted/50 rounded">
                                <span class="text-sm"
                                    x-text="member.name + (member.location ? ' (' + member.location + ')' : '')"></span>
                                <button @click="removeMember(member.id)"
                                    class="text-destructive hover:bg-destructive/10 p-1 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-user-minus">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <line x1="23" x2="17" y1="11" y2="11" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <label class="text-xs font-medium mb-1 block text-muted-foreground">Add New Members</label>
                    <input type="text" x-model="userSearchQuery" @input.debounce.300ms="searchUsersForGroup()"
                        placeholder="Search users..."
                        class="w-full px-3 py-2 text-sm rounded-md border border-input bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring mb-2">
                    <div class="max-h-32 overflow-y-auto border rounded-md p-2 space-y-1">
                        <template x-for="user in availableUsers" :key="user.id">
                            <div class="flex items-center justify-between p-2 hover:bg-muted/50 rounded"
                                x-show="!groupMembers.some(m => m.id == user.id)">
                                <span class="text-sm"
                                    x-text="user.name + (user.location ? ' (' + user.location + ')' : '')"></span>
                                <button @click="addMember(user.id)"
                                    class="text-primary hover:bg-primary/10 p-1 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-user-plus">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <line x1="19" x2="19" y1="8" y2="14" />
                                        <line x1="22" x2="16" y1="11" y2="11" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <button @click="deleteGroup()"
                        class="w-full py-2 bg-destructive text-destructive-foreground rounded-md text-sm font-medium hover:opacity-90">
                        Delete Group
                    </button>
                    <p class="text-[10px] text-muted-foreground text-center mt-2">Danger: This action cannot be undone.
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Messages View -->
        <div x-show="currentView === 'chat'" class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Messages Area -->
            <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-muted/20 min-h-0">
                <template x-if="loadingChat">
                    <div class="flex justify-center p-4">
                        <span class="text-xs text-muted-foreground">Loading messages...</span>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.send_by_you ? 'items-end' : 'items-start'">
                        <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm shadow-sm relative group"
                            :class="msg.send_by_you ? 'bg-primary text-primary-foreground rounded-br-none' : 'bg-white dark:bg-zinc-800 border border-border rounded-bl-none'">

                            <!-- Parent Message Quote -->
                            <template x-if="msg.parent_message_id">
                                <div
                                    class="mb-2 p-2 rounded bg-black/10 dark:bg-white/10 text-xs border-l-2 border-primary/50">
                                    <div class="opacity-70 truncate" x-html="msg.parent_message_body || 'Attachment'">
                                    </div>
                                </div>
                            </template>

                            <!-- Image Attachment -->
                            <template x-if="msg.is_image">
                                <div class="mb-2 mt-1">
                                    <img :src="msg.s3_url"
                                        class="rounded-lg max-h-48 object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                        @click="window.open(msg.s3_url, '_blank')">
                                </div>
                            </template>

                            <!-- File Attachment -->
                            <template x-if="msg.attachment && !msg.is_image">
                                <a :href="msg.s3_url" target="_blank"
                                    class="flex items-center gap-2 mb-2 bg-black/10 dark:bg-white/10 p-2 rounded hover:bg-black/20 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-paperclip">
                                        <path
                                            d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                    </svg>
                                    <span class="truncate underline decoration-dotted"
                                        x-text="msg.type || 'File'"></span>
                                </a>
                            </template>

                            <!-- Sender Name for Groups -->
                            <template x-if="currentUser && currentUser.group_id != '0' && !msg.send_by_you">
                                <div class="text-[10px] font-bold text-primary mb-1">
                                    <span x-text="msg.name"></span>
                                    <template x-if="msg.location">
                                        <span x-text="' (' + msg.location + ')'"
                                            class="font-normal opacity-75 ml-0.5"></span>
                                    </template>
                                </div>
                            </template>

                            <div x-html="msg.body" class="whitespace-pre-wrap break-words"></div>

                            <span class="text-[10px] opacity-70 block text-right mt-1" x-text="msg.created_at"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-background border-t border-border z-20 relative">

                <!-- Attachment Preview -->
                <div x-show="attachment" class="mb-2 flex items-center gap-2 bg-muted p-2 rounded-lg">
                    <span class="text-xs truncate flex-1" x-text="attachment ? attachment.name : ''"></span>
                    <button @click="clearAttachment" class="text-muted-foreground hover:text-destructive">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-x">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-end gap-2">
                    <button @click="$refs.fileInput.click()"
                        class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-full transition-colors flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-paperclip">
                            <path
                                d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                        </svg>
                    </button>
                    <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden">

                    <textarea x-model="newMessage" @keydown.enter.prevent="sendMessage()"
                        placeholder="Type a message..." rows="1"
                        class="flex-1 max-h-32 min-h-[40px] py-2 px-3 text-sm bg-muted/50 border-0 rounded-2xl focus:ring-0 focus:bg-muted resize-none overflow-hidden"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"></textarea>

                    <button @click="sendMessage()" :disabled="!newMessage.trim() && !attachment"
                        class="p-2 bg-primary text-primary-foreground rounded-full hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-send-horizontal">
                            <path d="m3 3 3 9-3 9 19-9Z" />
                            <path d="M6 12h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Toggle Button -->
    <button @click="toggleChat()"
        class="h-14 w-14 rounded-full bg-primary text-primary-foreground shadow-2xl flex items-center justify-center hover:scale-105 transition-transform duration-200 focus:outline-none focus:ring-4 focus:ring-primary/20 relative group">

        <span class="absolute -top-1 -right-1 flex h-4 w-4" x-show="totalUnread > 0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span
                class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] font-bold text-white items-center justify-center"
                x-text="totalUnread > 9 ? '9+' : totalUnread"></span>
        </span>

        <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-message-circle">
            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
        </svg>
        <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-chevron-down">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>
</div>

<script>
    function chatWidget() {
        return {
            isOpen: false,
            currentView: 'list', // 'list', 'chat', 'create_group'
            users: [],
            messages: [],
            currentUser: null,
            searchQuery: '',
            newMessage: '',
            attachment: null,
            loadingUsers: false,
            loadingChat: false,
            pollInterval: null,
            totalUnread: 0,

            // Group Creation State
            newGroupName: '',
            editGroupName: '',
            userSearchQuery: '',
            availableUsers: [],
            selectedGroupMembers: [],
            groupMembers: [],

            get headerTitle() {
                if (this.currentView === 'create_group') return 'Create Group';
                if (this.currentView === 'edit_group') return 'Group Settings';
                if (this.currentView === 'chat' && this.currentUser) {
                    let title = this.currentUser.name;
                    if (this.currentUser.location) {
                        title += ` (${this.currentUser.location})`;
                    }
                    return title;
                }
                return 'Messages';
            },

            init() {
                this.fetchUsers();
                setInterval(() => {
                    if (!this.isOpen || this.currentView === 'list') {
                        this.fetchUsers(true);
                    } else if (this.isOpen && this.currentView === 'chat' && this.currentUser) {
                        this.fetchChat(this.currentUser, true);
                    }
                }, 10000);
            },

            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.fetchUsers();
                    if (this.currentView === 'chat') {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                }
            },

            setView(view) {
                this.currentView = view;
                if (view === 'list') {
                    this.currentUser = null;
                    this.messages = [];
                    this.fetchUsers();
                } else if (view === 'create_group') {
                    this.newGroupName = '';
                    this.selectedGroupMembers = [];
                    this.userSearchQuery = '';
                    this.searchUsersForGroup();
                } else if (view === 'edit_group') {
                    this.editGroupName = this.currentUser.name;
                    this.groupMembers = [];
                    this.fetchGroupMembers();
                    this.userSearchQuery = '';
                    this.searchUsersForGroup();
                }
            },

            async fetchGroupMembers() {
                try {
                    const response = await fetch(`/chat/group/view_members?group_id=${this.currentUser.group_id}`);
                    const data = await response.json();
                    if (data.success) {
                        this.groupMembers = data.member_data || [];
                    }
                } catch (error) {
                    console.error('Error fetching group members:', error);
                }
            },

            async updateGroupName() {
                if (!this.editGroupName.trim()) return;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/chat/group/update/${this.currentUser.group_id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ name: this.editGroupName })
                    });
                    const data = await response.json();
                    if (data.data) {
                        this.currentUser.name = this.editGroupName;
                        alert('Group name updated');
                    }
                } catch (error) {
                    console.error('Error updating group name:', error);
                }
            },

            async addMember(userId) {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/chat/group/add_members/${this.currentUser.group_id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ members_ids: [userId] })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.fetchGroupMembers();
                    }
                } catch (error) {
                    console.error('Error adding member:', error);
                }
            },

            async removeMember(userId) {
                if (!confirm('Remove this member?')) return;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/chat/group/remove_members/${this.currentUser.group_id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ members_ids: [userId] })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.fetchGroupMembers();
                    }
                } catch (error) {
                    console.error('Error removing member:', error);
                }
            },

            async deleteGroup() {
                if (!confirm('Are you absolutely sure you want to delete this group? All history will be lost for everyone.')) return;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`/chat/group/${this.currentUser.group_id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.setView('list');
                    }
                } catch (error) {
                    console.error('Error deleting group:', error);
                }
            },

            async searchUsersForGroup() {
                try {
                    // We can reuse get_users endpoint or a dedicated search endpoint.
                    // The get_users endpoint returns people you have chatted with OR search results.
                    // Let's use get_users with search param.
                    const response = await fetch(`/chat/get_users?search=${this.userSearchQuery}`);
                    const data = await response.json();
                    if (data.success) {
                        // Filter out existing groups. Items have 'recipient_id' if they are users.
                        // Use loose equality for group_id check to handle int vs string 0
                        this.availableUsers = data.data.filter(u => u.recipient_id && u.group_id == 0);
                    }
                } catch (error) {
                    console.error('Error searching users:', error);
                }
            },

            async createGroup() {
                if (!this.newGroupName || this.selectedGroupMembers.length === 0) return;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch('/chat/group', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            name: this.newGroupName,
                            members_ids: this.selectedGroupMembers
                        })
                    });

                    const data = await response.json();
                    if (data.data) { // Check for success
                        // Refresh list and go back
                        this.setView('list');
                    }
                } catch (error) {
                    console.error('Error creating group:', error);
                    alert('Failed to create group');
                }
            },

            async fetchUsers(background = false) {
                if (!background) this.loadingUsers = true;

                try {
                    const response = await fetch(`/chat/get_users?search=${this.searchQuery}`);
                    const data = await response.json();

                    if (data.success) {
                        this.users = data.data;
                        this.totalUnread = data.total_unread_msg || 0;
                    }
                } catch (error) {
                    console.error('Error fetching users:', error);
                } finally {
                    this.loadingUsers = false;
                }
            },

            selectUser(user) {
                this.currentUser = user;
                this.currentView = 'chat';
                this.fetchChat(user);
                this.markAsRead(user);
            },

            async fetchChat(user, background = false) {
                if (!background) {
                    this.loadingChat = true;
                    this.messages = [];
                }

                try {
                    let recipientId = user.recipient_id || user.id;
                    let recipientType = (user.group_id && user.group_id != '0') ? 1 : 0;

                    if (user.group_id && user.group_id != '0') {
                        recipientId = user.group_id;
                        recipientType = 1;
                    } else if (user.recipient_id) {
                        recipientId = user.recipient_id;
                        recipientType = 0;
                    }

                    const response = await fetch(`/chat/get_chat?id=${recipientId}&type=${recipientType}`);
                    const data = await response.json();

                    if (data.success) {
                        const newMessages = data.data.message || [];
                        if (background) {
                            if (newMessages.length !== this.messages.length) {
                                this.messages = newMessages;
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        } else {
                            this.messages = newMessages;
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    }
                } catch (error) {
                    console.error('Error fetching chat:', error);
                } finally {
                    this.loadingChat = false;
                }
            },

            async sendMessage() {
                if ((!this.newMessage.trim() && !this.attachment) || !this.currentUser) return;

                const formData = new FormData();
                formData.append('body', this.newMessage);

                let recipientId = this.currentUser.recipient_id || this.currentUser.id;
                let recipientType = (this.currentUser.group_id && this.currentUser.group_id != '0') ? 1 : 0;

                // Logic fix: Ensure we prioritize group_id if it exists and isn't 0
                if (this.currentUser.group_id && this.currentUser.group_id != '0') {
                    recipientId = this.currentUser.group_id;
                    recipientType = 1;
                } else if (this.currentUser.recipient_id) {
                    recipientId = this.currentUser.recipient_id;
                    recipientType = 0;
                }

                // Explicitly cast to string/int for FormData assurance, though JS handles it
                formData.append('id', recipientId);
                formData.append('type', recipientType);
                formData.append('user_type', recipientType);

                if (this.attachment) {
                    formData.append('attachment', this.attachment);
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                this.newMessage = '';
                const tempAttachment = this.attachment; // keep ref for error rollback if needed
                this.attachment = null;
                this.$refs.fileInput.value = '';

                try {
                    const response = await fetch('/chat', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                            // No Content-Type header! Browser sets it with boundary for FormData
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.fetchChat(this.currentUser, true);
                    } else {
                        // Handle server-side validation error
                        if (data.message) alert(data.message);
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Failed to send message');
                }
            },

            async markAsRead(user) {
                let recipientId = user.recipient_id || user.id;
                let recipientType = (user.group_id && user.group_id != '0') ? 1 : 0;
                if (user.group_id && user.group_id != '0') {
                    recipientId = user.group_id;
                    recipientType = 1;
                } else if (user.recipient_id) {
                    recipientId = user.recipient_id;
                    recipientType = 0;
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                await fetch('/chat/mark_as_read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ id: recipientId, type: recipientType })
                });
            },

            handleFileSelect(e) {
                if (e.target.files.length > 0) {
                    this.attachment = e.target.files[0];
                }
            },

            clearAttachment() {
                this.attachment = null;
                this.$refs.fileInput.value = '';
            },

            scrollToBottom() {
                const container = this.$refs.messagesContainer;
                container.scrollTop = container.scrollHeight;
            },

            getInitials(name) {
                if (!name) return '?';
                return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            }
        }
    }
</script>