# Gamebank

A simple banking site for use in games, based on PHP Fat Free Framework, Bootstrap 4 and SQLite. 

# Banking features

To understand this system, consider the following:
  - OWNERS have one or more ACCOUNTS. OWNERS have a personal password. 
  - To log in, either enter an account number and owner password, or answer the security questions for the given account if there are any. 
  - If a user enters a wrong password or fails to answers the questions correctly, the IP address is banned from the site for a limited amount of time
  - Once logged in, you can see the transactions for that user and send "money" to other accounts, as long as you have money in your account
  
 
# Admin screen
An admin interface is available at /logging which has limited functionality:
- connect to the SQLITE admin page
- flush the database with the content in the SQL import file
- release all blocked IP addresses
