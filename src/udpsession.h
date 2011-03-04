#ifndef _GZK_UDP_SESSION_H_
#define _GZK_UDP_SESSION_H_
#include <iostream>
using namespace std;

class UdpSession
{
	public:
		UdpSession(string ip,int port);
		~UdpSession();
		void process();
		void reloadtt();
	private:
		int m_socket;
		char m_data[1024];
};

#endif
