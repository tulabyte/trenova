app.service('FTAFunctions', function (Data, $state) {

	var self = this;

	this.test = function() {
		return "FTAFunctions works!";
	};

	/* ADMINS */
	// get selected admin
	this.getAdmin = function (id) {
		return Data.get('getAdmin?id='+id);
	};

	// get list of admins
	this.getAdminList = function (type, status='ALL') {
		if(status != 'ALL') {
			return Data.get('getAdminList?type='+type+'&status='+status);
		} else {
			return Data.get('getAdminList?type='+type);
		}
	};

	// get admin logs
	this.getAdminLogs = function () {
		return Data.get('getAdminLogs');
	};

	this.getProfile = function () {
	return Data.get('getProfile');
	};

	// deleted selected admin
	this.deleteAdmin = function (id) {
		return Data.get('deleteAdmin?id='+id);
	};

	// verify selected admin
	this.verifyAdmin = function (id) {
		return Data.get('verifyAdmin?id='+id);
	};

	/* USERS */
	// get selected user
	this.getUser = function (id) {
		return Data.get('getUser?id='+id);
	};

	// get list of users
	this.getUserList = function () {
		return Data.get('getUserList');
	};

	// get list of feedbacks
	this.getFeedbackList = function () {
		return Data.get('getFeedbackList');
	};

	// get selected user
	this.getFeedbackDetails = function (id) {
		return Data.get('getFeedbackDetails?id='+id);
	};

	// deleted selected user
	this.deleteUser = function (id) {
		return Data.get('deleteUser?id='+id);
	};

	/* CATEGORIES */
	// get selected category
	this.getCategory = function (id) {
		return Data.get('getCategory?id='+id);
	};

	// get list of categories
	this.getCategoryList = function () {
		return Data.get('getCategoryList');
	};

	// deleted selected category
	this.deleteCategory = function (id) {
		return Data.get('deleteCategory?id='+id);
	};

	/* COURSES */
	// get selected course
	this.getCourse = function (id) {
		return Data.get('getCourse?id='+id);
	};

	// get list of courses
	this.getCourseList = function () {
		return Data.get('getCourseList');
	};

	// deleted selected course
	this.deleteCourse = function (id) {
		return Data.get('deleteCourse?id='+id);
	};

	// mark as paid
	this.markResellerCommission = function (id) {
		return Data.get('markResellerCommission?id='+id);
	};

	//delete file
	this.deleteFile = function (filename) {
		return Data.get('deleteFile?f='+filename);
	}

	//delete profile image
	this.deleteProfile = function (filename) {
		return Data.get('deleteProfile?f='+filename);
	}

	/* MODULES */
	// get list of modules for a course
	this.getModuleList = function (course_id) {
		return Data.get('getModuleList?course_id='+course_id);
	};

	// deleted selected module
	this.deleteModule = function (id) {
		return Data.get('deleteModule?id='+id);
	};

	// deleted selected question
	this.deleteQuestionModule = function (id) {
		return Data.get('deleteQuestionModule?id='+id);
	};

	/* DASHBOARD */

	// get dashboard box stats
	this.getDashStats = function () {
		return Data.get('getDashStats');
	};

	// get dashboard box stats
	this.getLatestNotifications = function () {
		return Data.get('getLatestNotifications');
	};

	// get latest subs for dash
	this.getLatestSubs = function () {
		return Data.get('getLatestSubs');
	};

	// get top users for dash
	this.getTopUsers = function () {
		return Data.get('getTopUsers');
	};

	// get top courses for dash
	this.getTopCourses = function () {
		return Data.get('getTopCourses');
	};

	// get new users for dash
	this.getNewUsers = function () {
		return Data.get('getNewUsers');
	};

	// get new payments for dash
	this.getNewPayments = function () {
		return Data.get('getNewPayments');
	};

	// get user and sub trends for Dash from API
	this.getDashTrends = function (period) {
		return Data.get('getDashTrends?start_date='+period.start_date+'&end_date='+period.end_date);
	};

	// get subscription list from API
	this.getSubscriptionList = function () {
		return Data.get('getSubscriptionList');
	};

	// get reseller details
	this.getResellerDetails = function (id) {
		return Data.get('getResellerDetails?id='+id);
	};

	// get reseller dashboard
	this.getResellerDashboard = function () {
		return Data.get('getResellerDashboard');
	};

	// get reseller's paid list
	this.getResellerPaid = function () {
		return Data.get('getResellerPaid');
	};

	// get reseller's unpaid list
	this.getResellerUnPaid = function () {
		return Data.get('getResellerUnPaid');
	};

	// get reseller list
	this.getResellerList = function () {
		return Data.get('getResellerList');
	};

	// get reseller commission list
	this.getResellerCommission = function () {
		return Data.get('getResellerCommission');
	};

	// get agent details
	this.getAgentDetails = function (id) {
		return Data.get('getAgentDetails?id='+id);
	};

	// get agent dashboard
	this.getAgentDash = function () {
		return Data.get('getAgentDash');
	};

	// get agent orders 
	this.getAgentOrder = function () {
		return Data.get('getAgentOrder');
	};

	// get Agent Course buyAgentCourse
	this.getAgentCourse = function () {
		return Data.get('getAgentCourse');
	};

	// buyAgentCourse
	this.buyAgentCourse = function (agent) {
		return Data.post('buyAgentCourse',{agent : agent});
	};

	// get agent purchases
	this.getAgentPurchase = function () {
		return Data.get('getAgentPurchase');
	};

	//getAgentUnusedPurchase
	this.getAgentUnusedPurchase = function () {
		return Data.get('getAgentUnusedPurchase');
	};
		// get order details
	this.getBuyDetails = function (id) {
		return Data.get('getBuyDetails?id='+id);
	};


	// get list of orders
	this.getOrderList = function () {
		return Data.get('getOrderList');
	};

	// get order details
	this.getOrder = function (id) {
		return Data.get('getOrder?id='+id);
	};

	// deleted selected order
	this.deleteOrder = function (id) {
		return Data.get('deleteOrder?id='+id);
	};

});