require 'socket'

s = TCPSocket.open('localhost', 9999)
s.puts('1,2')
s.close