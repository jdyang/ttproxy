#ifndef _TT_PROXY_SESSION_H_
#define _TT_PROXY_SESSION_H_
#include <vector>
#include <stdint.h>
#include <map>
#include "ttcircle.h"

using namespace std;

#define IOBUFSIZE 65536

namespace TTCMD
{
	static const uint16_t CMD_PUT = 0xc810;
	static const uint16_t CMD_PUTKEEP = 0xc811;
	static const uint16_t CMD_GET = 0xc830;
	static const uint16_t CMD_MISC = 0xc890;
	static const uint16_t CMD_VANISH = 0xc872;
};


class Session
{
	public:
		Session(int sock);
		~Session();
		//int Recv(int sock,void * data,int len);
		//int RecvByte(int sock);
		int Send(int sock,void * sdata,int len);
		void process();
		int do_vanish();
		//int do_put();
		//int do_get();
		int do_misc();
		int do_misc_out(int rnum);
		int do_misc_put(int runm);
		int do_misc_get(int rnum);
		int do_misc_setindex(int rnum);
		int do_misc_search(int rnum,int prnum);
		int do_misc_search_count(map<string ,TTCircle*>::iterator iter);
		int do_misc_search_out(map<string ,TTCircle*>::iterator iter);
		int do_misc_search_limit(map<string ,TTCircle*>::iterator iter,int mpmax,int skip,int max);	
		int do_misc_search_all(map<string ,TTCircle*>::iterator iter);
	public:
		int m_socket;
		vector<char> m_data;
		vector<char> m_rdata;
		int m_picur;
		int m_pib;
		int m_pircur;
		int m_pirb;
};
#endif
