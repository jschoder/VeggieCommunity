/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

// Ajax Chat language Object:
var ajaxChatLang = {
	
	login: '%s masuk ke chat.',
	logout: '%s keluar dari chat.',
	logoutTimeout: '%s telah keluar dari saluran (Timeout).',
	logoutIP: '%s elah keluar dari saluran (Invalid IP address).',
	logoutKicked: '%s elah keluar dari saluran (Kicked).',
	channelEnter: '%s masuk saluran.',
	channelLeave: '%s meninggalkan.',
	privmsg: '(berbisik)',
	privmsgto: '(berbisik ke %s)',
	invite: '%s mengundang anda untuk gabung ke %s.',
	inviteto: 'Undangan anda ke %s untuk gabung saluran %s telah dikirim.',
	uninvite: '%s membatalkan untuk mengundang anda dari saluran %s.',
	uninviteto: 'Pembatalan undangan anda ke %s untuk salura %s telah dikirim.',	
	queryOpen: 'Saluran Privasi telah dibuka untuk %s.',
	queryClose: 'Saluran Privasi untuk %s telah ditutup.',
	ignoreAdded: 'Menambah %s ke daftar yang diacuhkan.',
	ignoreRemoved: 'Mencabut %s dari daftar yang diacuhkan.',
	ignoreList: 'Acuhkan pengguna:',
	ignoreListEmpty: 'Tidak ada nama dalam daftar yang diacuhkan.',
	who: 'Pengguna online:',
	whoChannel: 'Pengguna-2 yang online di saluran %s:',
	whoEmpty: 'Tidak ada pengguna yang online di saluran tersebut.',
	list: 'Saluran yang tersedia:',
	bans: 'Pengguna yang diblok:',
	bansEmpty: 'Tidak ada pengguna yang diblok.',
	unban: 'Pembatalan Blok pengguna %s .',
	whois: 'Alamat IP Pengguna %s :',
	whereis: 'Pengguna %s ada di saluran %s.',
	roll: '%s melempar %s dan mendapatkan %s.',
	nick: '%s sekarang dikenal sebagai %s.',
	toggleUserMenu: 'Tombol menu pengguna untuk %s',
	userMenuLogout: 'Keluar',
	userMenuWho: 'Daftar pengguna yang online',
	userMenuList: 'Daftar saluran-saluran yang tersedia',
	userMenuAction: 'Menjelaskan tindakan',
	userMenuRoll: 'Melempar Dadu',
	userMenuNick: 'Mengganti Nama',
	userMenuEnterPrivateRoom: 'Memasuki ruang privasi',
	userMenuSendPrivateMessage: 'Kirim pesan pribadi',
	userMenuDescribe: 'Kirim tindakan pribadi',
	userMenuOpenPrivateChannel: 'Buka Saluran Privasi',
	userMenuClosePrivateChannel: 'Tutup Saluran Privasi',
	userMenuInvite: 'Mengundang',
	userMenuUninvite: 'Tidak Mengundang',
	userMenuIgnore: 'Acuhkan/Terima',
	userMenuIgnoreList: 'Daftar Pengguna yang diacuhkan',
	userMenuWhereis: 'Tampilkan Saluran',
	userMenuKick: 'Tendang/Blok',
	userMenuBans: 'Daftar Pengguna yang diblok',
	userMenuWhois: 'Tampilkan IP',
	unbanUser: 'Batalkan blok pengguna %s',
	joinChannel: 'Gabung Saluran %s',
	cite: '%s berkata:',
	urlDialog: 'Mohon masukan alamat (URL) of the web:',
	deleteMessage: 'Hapus pesan chat ini',
	deleteMessageConfirm: 'Yakin akan menghapus pesan chat yang dipilih?',
	errorCookiesRequired: 'Cookies diperlukan untuk chat.',
	errorUserNameNotFound: 'Error: Pengguna %s tidak ada.',
	errorMissingText: 'Error: Teks pesan tidak ada.',
	errorMissingUserName: 'Error: Nama tidak ada.',
	errorInvalidUserName: 'Error: Kesalahan pada Nama.',
	errorUserNameInUse: 'Error: Nama telah dipakai.',
	errorMissingChannelName: 'Error: Nama saluran belum ada.',
	errorInvalidChannelName: 'Error: Kesalahan pada nama saluran: %s',
	errorPrivateMessageNotAllowed: 'Error: Pesan pribadi tidak diijinkan.',
	errorInviteNotAllowed: 'Error: Anda tidak diijinkan untuk mengundang orang lain ke saluran ini.',
	errorUninviteNotAllowed: 'Error: Anda tidak diijinkan untuk tidak mengundang seseorang ke saluran ini.',
	errorNoOpenQuery: 'Error: Tidak ada saluran privasi yang dibuka.',
	errorKickNotAllowed: 'Error: Anda tidak diijinkan untuk menendang %s.',
	errorCommandNotAllowed: 'Error: Perintah tidak diijinkan: %s',
	errorUnknownCommand: 'Error: Perintah tidak diketahui: %s',
	errorMaxMessageRate: 'Error: Anda telah melampaui batas pesan maksimum per menitnya.',
	errorConnectionTimeout: 'Error: Koneksi putus. Mohon dicoba kembali.',
	errorConnectionStatus: 'Error: Status koneksi: %s',
	errorSoundIO: 'Error: Gagal mengeluarkan suara (Flash IO Error).',
	errorSocketIO: 'Error: Gagal mengadakan koneksi ke server (Flash IO Error).',
	errorSocketSecurity: 'Error: Gagal mengadakan koneksi ke server (Flash Security Error).',
	errorDOMSyntax: 'Error: Sintaks DOM yang tidak dikenal(DOM ID: %s).'
	
}